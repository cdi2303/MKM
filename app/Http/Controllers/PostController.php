<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('user_id', Auth::id())->get();

        $query = Post::where('user_id', Auth::id())->orderBy('id','desc');

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $posts = $query->get();

        return view('posts.index', compact('posts','projects'));
    }

    public function show($id)
    {
        $post = Post::where('id',$id)->where('user_id',Auth::id())->firstOrFail();
        return view('posts.show', compact('post'));
    }

    public function drafts()
    {
        $drafts = Post::where('user_id', Auth::id())
                    ->where('is_draft', true)
                    ->orderBy('id', 'desc')
                    ->get();

        return view('drafts.index', compact('drafts'));
    }

    public function editDraft($id)
    {
        $post = Post::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->where('is_draft', true)
                    ->firstOrFail();

        $projects = Project::where('user_id', Auth::id())->get();

        return view('drafts.edit', compact('post', 'projects'));
    }

    public function versions($id)
    {
        $post = Post::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $versions = \App\Models\PostVersion::where('post_id', $post->id)
                        ->orderBy('version', 'desc')
                        ->get();

        return view('posts.versions', compact('post', 'versions'));
    }

    public function versionDetail($id, $version)
    {
        $post = Post::findOrFail($id);
        $ver = PostVersion::where('post_id', $id)
                        ->where('version', $version)
                        ->firstOrFail();

        return view('posts.version_detail', compact('post', 'ver'));
    }
    
    public function restoreVersion($id, $version)
    {
        $ver = PostVersion::where('post_id', $id)
                        ->where('version', $version)
                        ->firstOrFail();

        $post = Post::findOrFail($id);

        $post->update([
            'title' => $ver->title,
            'keyword' => $ver->keyword,
            'html' => $ver->html,
            'content' => $ver->content,
            'meta' => $ver->meta,
        ]);

        // 복원 후 새 버전 생성
        $this->saveVersion($post);

        return redirect("/posts/{$id}")
            ->with('success', "버전 {$version} 으로 복원 완료");
    }

}
