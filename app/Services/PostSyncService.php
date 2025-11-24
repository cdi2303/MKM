<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostSyncService
{
    public function syncAll()
    {
        $posts = Post::whereNotNull('external_slug')->get();

        foreach ($posts as $post) {
            if ($post->platform === 'wordpress') {
                $this->syncWordPress($post);
            }

            if ($post->platform === 'tistory') {
                $this->syncTistory($post);
            }
        }
    }

    private function syncWordPress(Post $post)
    {
        try {
            $url = "{$post->wp_api_url}/wp-json/wp/v2/posts/{$post->external_id}";

            $res = Http::get($url)->json();

            if (! $res) return;

            $views   = $res['meta']['views']   ?? null;
            $clicks  = $res['meta']['clicks']  ?? null;

            $post->views = $views;
            $post->clicks = $clicks;

            $post->ctr = $this->calculateCTR($views, $clicks);
            $post->last_synced_at = now();
            $post->save();

        } catch (\Exception $e) {
            Log::error('WP Sync Error: '.$e->getMessage());
        }
    }

    private function syncTistory(Post $post)
    {
        try {
            $api = "https://www.tistory.com/apis/post/statistics";
            $res = Http::get($api, [
                'access_token' => $post->tistory_access_token,
                'blogName'     => $post->blog_name,
                'postId'       => $post->external_id
            ])->json();

            if (! $res || $res['tistory']['status'] != '200') return;

            $data = $res['tistory']['item'];

            $views = $data['readCount'] ?? null;
            $likes = $data['likeCount'] ?? null;
            $comments = $data['commentCount'] ?? null;

            // 클릭수는 티스토리에서 따로 제공 안하므로,
            // 추후 Search Console API 연동 가능
            $clicks = $post->clicks ?? null;

            $post->views = $views;
            $post->likes = $likes;
            $post->comments = $comments;

            $post->ctr = $this->calculateCTR($views, $clicks);
            $post->last_synced_at = now();
            $post->save();

        } catch (\Exception $e) {
            Log::error('Tistory Sync Error: '.$e->getMessage());
        }
    }

    private function calculateCTR($views, $clicks)
    {
        if (!$views || !$clicks || $views == 0) return null;
        return round(($clicks / $views) * 100, 2);
    }
}
