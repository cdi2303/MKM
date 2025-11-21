<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', Auth::id())->get();
        return view('projects.index', compact('projects'));
    }

    public function show($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $posts = $project->posts()->orderBy('id', 'desc')->get();

        return view('projects.show', compact('project', 'posts'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        Project::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect('/projects');
    }

    public function edit($id)
    {
        $project = Project::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $project->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect('/projects');
    }

    public function destroy(Request $request, $id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // 옵션: delete_posts 파라미터가 1이면 글도 같이 삭제
        if ($request->delete_posts == 1) {
            // 프로젝트의 모든 글 삭제
            $project->posts()->delete();
        } else {
            // 글은 남기고 project_id만 NULL로 변경
            $project->posts()->update([ 'project_id' => null ]);
        }

        // 프로젝트 삭제
        $project->delete();

        return redirect('/projects')->with('success', '프로젝트 삭제 완료');
    }

    public function stats($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // 전체 글
        $posts = $project->posts()->orderBy('id', 'desc')->get();

        $totalPosts = $posts->count();

        // 최근 글
        $latestPost = $posts->first();
        $latestDate = $latestPost ? $latestPost->created_at->format('Y-m-d H:i') : '-';

        // 월별 통계
        $monthly = $posts->groupBy(function($p){
            return $p->created_at->format('Y-m');
        })->map->count();

        // 키워드 TOP 10
        $topKeywords = $posts->groupBy('keyword')
            ->map->count()
            ->sortDesc()
            ->take(10);

        // SEO 점수 (meta 컬럼에 있다고 가정)
        $seoScores = $posts->pluck('meta')->map(function($meta){
            return is_array($meta) && isset($meta['score']) ? $meta['score'] : null;
        })->filter()->values();

        $avgSeoScore = $seoScores->avg();

        return view('projects.stats', compact(
            'project',
            'totalPosts',
            'latestDate',
            'monthly',
            'topKeywords',
            'avgSeoScore'
        ));
    }

}
