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
            return redirect()->back()->with('success', '워드프레스 업로드 완료');
        } else {
            return redirect()->back()->with('error', '워드프레스 업로드 실패: ' . $data);
        }
    }

    public function publishTistory($id, TistoryPublisher $publisher)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        [$ok, $data] = $publisher->publish($post);

        if ($ok) {
            return redirect()->back()->with('success', '티스토리 업로드 완료');
        } else {
            return redirect()->back()->with('error', '티스토리 업로드 실패: ' . $data);
        }
    }
}
