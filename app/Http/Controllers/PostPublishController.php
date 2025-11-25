<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\WordPressPublisher;
use App\Services\TistoryPublisher;
use Illuminate\Support\Facades\Auth;

class PostPublishController extends Controller
{
    public function publishWordpress($id, WordPressPublisher $publisher)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        [$ok, $data] = $publisher->publish($post);

        if ($ok) {

            // ★ WordPress URL 추출
            $url = $data['link'] ?? null;

            $post->update([
                'platform'     => 'wordpress',
                'external_id'  => $data['id'] ?? null,
                'external_slug'=> $data['slug'] ?? null,
                'external_url' => $url,
            ]);

            return redirect()
                ->back()
                ->with('success', "워드프레스 업로드 완료\nURL: {$url}");
        }

        return redirect()->back()->with('error', '워드프레스 업로드 실패: '.$data);
    }



    public function publishTistory($id, TistoryPublisher $publisher)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        [$ok, $data] = $publisher->publish($post);

        if ($ok) {

            // ★ 티스토리 URL 생성
            $url = null;
            if (!empty($data['tistory']['url'])) {
                $url = $data['tistory']['url'];
            }

            $post->update([
                'platform'     => 'tistory',
                'external_id'  => $data['tistory']['postId'] ?? null,
                'external_url' => $url,
            ]);

            return redirect()
                ->back()
                ->with('success', "티스토리 업로드 완료\nURL: {$url}");
        }

        return redirect()->back()->with('error', '티스토리 업로드 실패: '.$data);
    }
}
