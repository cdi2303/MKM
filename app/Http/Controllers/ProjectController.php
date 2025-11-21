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

        $posts = $project->posts()->orderBy('id','desc')->get();

        $totalPosts = $posts->count();
        $latestPost = $posts->first();
        $latestDate = $latestPost ? $latestPost->created_at->format('Y-m-d H:i') : '-';

        // 키워드 TOP 5
        $topKeywords = $posts
            ->groupBy('keyword')
            ->sortByDesc(function($group){ return count($group); })
            ->take(5)
            ->map(function($group, $keyword){
                return [
                    'keyword' => $keyword,
                    'count' => count($group)
                ];
            });

        // 최근 글 5개
        $recentPosts = $posts->take(5);


        // ------------------------------
        // ⭐ 추가되는 그래프용 데이터
        // ------------------------------

        // 1) 키워드 사용 빈도 전체
        $keywordStats = $posts
            ->groupBy('keyword')
            ->map(fn($group) => $group->count());

        // 2) 날짜별 글 생성량 (Y-m-d 기준)
        $dateStats = $posts
            ->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map(fn($group) => $group->count());

        // 3) 최근 30일 데이터 만들기
        $dailyStats = collect();
        $today = now();

        for ($i = 29; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i)->format('Y-m-d');
            $dailyStats[$day] = $dateStats[$day] ?? 0;
        }

        return view('projects.stats', compact(
            'project',
            'totalPosts',
            'latestDate',
            'topKeywords',
            'recentPosts',
            'keywordStats',
            'dateStats',
            'dailyStats'
        ));
    }
}
