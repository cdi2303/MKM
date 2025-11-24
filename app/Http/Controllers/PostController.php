<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\PostVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /* ----------------------------------------------
        게시글 목록
    ---------------------------------------------- */
    public function index(Request $request)
    {
        $projects = Project::where('user_id', Auth::id())->get();

        $query = Post::where('user_id', Auth::id())
            ->where('is_draft', false)
            ->orderBy('id', 'desc');

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $posts = $query->get();

        return view('posts.index', compact('posts', 'projects'));
    }

    /* ----------------------------------------------
        게시글 상세
    ---------------------------------------------- */
    public function show($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $versions = PostVersion::where('post_id', $post->id)
            ->orderBy('version','desc')
            ->get();

        return view('posts.show', compact('post', 'versions'));
    }

    /* ----------------------------------------------
        Draft 목록
    ---------------------------------------------- */
    public function drafts()
    {
        $drafts = Post::where('user_id', Auth::id())
            ->where('is_draft', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('drafts.index', compact('drafts'));
    }

    /* ----------------------------------------------
        Draft 편집 페이지
    ---------------------------------------------- */
    public function editDraft($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_draft', true)
            ->firstOrFail();

        $projects = Project::where('user_id', Auth::id())->get();

        return view('drafts.edit', compact('post', 'projects'));
    }

    /* ----------------------------------------------
        Draft 업데이트
    ---------------------------------------------- */
    public function updateDraft(Request $request, $id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_draft', true)
            ->firstOrFail();

        $post->update([
            'project_id' => $request->project_id,
            'keyword' => $request->keyword,
            'title' => $request->title,
            'html' => $request->html,
            'content' => strip_tags($request->html),
        ]);

        return redirect('/drafts')->with('success', 'Draft 저장 완료');
    }

    /* ----------------------------------------------
        Draft → Post 전환
    ---------------------------------------------- */
    public function publishDraft($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_draft', true)
            ->firstOrFail();

        $post->update([
            'is_draft' => false,
            'meta' => [
                'description' => mb_substr($post->content, 0, 150)
            ]
        ]);

        $this->saveVersion($post);

        return redirect('/posts')->with('success', '게시글로 변환 완료');
    }

    /* ----------------------------------------------
        Draft 삭제
    ---------------------------------------------- */
    public function deleteDraft($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_draft', true)
            ->firstOrFail();

        $post->delete();

        return redirect('/drafts')->with('success', 'Draft 삭제 완료');
    }

    /* ----------------------------------------------
        버전 목록
    ---------------------------------------------- */
    public function versions($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $versions = PostVersion::where('post_id', $post->id)
            ->orderBy('version', 'desc')
            ->get();

        return view('posts.versions', compact('post', 'versions'));
    }

    /* ----------------------------------------------
        버전 상세 보기
    ---------------------------------------------- */
    public function versionDetail($id, $version)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $ver = PostVersion::where('post_id', $id)
            ->where('version', $version)
            ->firstOrFail();

        return view('posts.version_detail', compact('post', 'ver'));
    }

    /* ----------------------------------------------
        버전 복원
    ---------------------------------------------- */
    public function restoreVersion($id, $version)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $ver = PostVersion::where('post_id', $id)
            ->where('version', $version)
            ->firstOrFail();

        $post->update([
            'title' => $ver->title,
            'keyword' => $ver->keyword,
            'html' => $ver->html,
            'content' => $ver->content,
            'meta' => $ver->meta
        ]);

        // 복원 후 새로운 버전 생성
        $this->saveVersion($post);

        return redirect("/posts/{$id}")
            ->with('success', "버전 {$version} 으로 복원 완료");
    }

    /* ----------------------------------------------
        버전 저장 함수 (공통)
    ---------------------------------------------- */
    private function saveVersion($post)
    {
        $latest = PostVersion::where('post_id', $post->id)
            ->orderBy('version', 'desc')
            ->first();

        $versionNumber = $latest ? $latest->version + 1 : 1;

        PostVersion::create([
            'post_id' => $post->id,
            'version' => $versionNumber,
            'title' => $post->title,
            'keyword' => $post->keyword,
            'html' => $post->html,
            'content' => $post->content,
            'meta' => $post->meta
        ]);
    }

    public function health($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $prompt = "
        너는 글 품질 전문가다.

        아래 HTML 콘텐츠를 분석하여 '글 품질 점검' 리포트를 작성하라.

        콘텐츠:
        {$post->html}

        출력 형식(JSON):
        {
            \"readability_score\": number (0~100),
            \"structure\": {
                \"h1\": number,
                \"h2\": number,
                \"h3\": number,
                \"paragraphs\": number,
                \"avg_paragraph_length\": number
            },
            \"problems\": [
                \"문제1\", \"문제2\", ...
            ],
            \"improvements\": [
                \"개선안1\", \"개선안2\", ...
            ],
            \"fixed_html\": \"개선된 HTML 전체\"
        }

        절대 설명 없이 JSON만 출력.
    ";

        $report = app(\App\Services\OpenAIService::class)->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a writing quality expert.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $json = json_decode($report, true);

        return view('posts.health', [
            'post' => $post,
            'data' => $json
        ]);
    }

    public function fixHealth(Request $request, $id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $fixedHtml = $request->fixed_html;

        $post->update([
            'html' => $fixedHtml,
            'content' => strip_tags($fixedHtml),
        ]);

        return response()->json(['result' => true]);
    }
}
