<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\WordPressPublisher;
use App\Services\TistoryPublisher;
use Illuminate\Support\Facades\Auth;

class PostPublishController extends Controller
{
    /**
     * 워드프레스 발행
     */
    public function publishWordpress($id, WordPressPublisher $publisher)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        [$ok, $data] = $publisher->publish($post);

        if (!$ok) {
            return redirect()->back()->with('error', '워드프레스 업로드 실패: ' . $data);
        }

        /*
        |--------------------------------------------------------------------------
        | WordPress API 반환값 예시
        |--------------------------------------------------------------------------
        | $data = [
        |   'id'      => 123,
        |   'slug'    => 'my-test-post',
        |   'link'    => 'https://example.com/my-test-post/',
        |   ...
        | ]
        |
        */

        $post->update([
            'platform'      => 'wordpress',
            'external_id'   => $data['id']    ?? null,
            'external_slug' => $data['slug']  ?? null,
            'wp_api_url'    => $data['link']  ?? null,
            'last_synced_at'=> now()
        ]);

        return redirect()->back()->with('success', '워드프레스 업로드 완료!');
    }



    /**
     * 티스토리 발행
     */
    public function publishTistory($id, TistoryPublisher $publisher)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        [$ok, $data] = $publisher->publish($post);

        if (!$ok) {
            return redirect()->back()->with('error', '티스토리 업로드 실패: ' . $data);
        }

        /*
        |--------------------------------------------------------------------------
        | Tistory API 반환 예시
        |--------------------------------------------------------------------------
        | $data = [
        |   'tistory' => [
        |       'postId' => 123,
        |       'url'    => 'https://myblog.tistory.com/123',
        |   ]
        | ]
        |
        */

        $post->update([
            'platform'      => 'tistory',
            'external_id'   => $data['tistory']['postId'] ?? null,
            'external_slug' => null,
            'wp_api_url'    => null,
            'tistory_access_token' => env('TISTORY_ACCESS_TOKEN'),
            'blog_name'     => env('TISTORY_BLOG_NAME'),
            'last_synced_at'=> now(),
            'meta' => array_merge($post->meta ?? [], [
                'tistory_url' => $data['tistory']['url'] ?? null
            ])
        ]);

        return redirect()->back()->with('success', '티스토리 발행 완료!');
    }
}
