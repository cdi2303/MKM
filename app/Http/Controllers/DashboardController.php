<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 전체 카운트
        $projectsCount = Project::where('user_id', $userId)->count();

        $postsQuery = Post::where('user_id', $userId);
        $postsCount = (clone $postsQuery)->where('is_draft', false)->count();
        $draftsCount = (clone $postsQuery)->where('is_draft', true)->count();

        // 평균 SEO 점수 (발행 글 기준)
        $seoScores = (clone $postsQuery)
            ->where('is_draft', false)
            ->get()
            ->map(fn($p) => $p->meta['seo_score'] ?? null)
            ->filter();

        $avgSeoScore = $seoScores->count() ? round($seoScores->avg(), 1) : null;

        // 최근 글 5개
        $recentPosts = (clone $postsQuery)
            ->where('is_draft', false)
            ->with('project')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 프로젝트 리스트 + 글 수
        $projects = Project::where('user_id', $userId)
            ->withCount(['posts' => function ($q) {
                $q->where('is_draft', false);
            }])
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard', compact(
            'projectsCount',
            'postsCount',
            'draftsCount',
            'avgSeoScore',
            'recentPosts',
            'projects'
        ));
    }
}
