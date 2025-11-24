<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenAIService;

class ProjectSeoController extends Controller
{
    protected OpenAIService $ai;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    public function index($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['posts' => function ($q) {
                $q->where('is_draft', false);
            }])
            ->firstOrFail();

        $posts = $project->posts;

        // 평균 SEO 점수
        $scores = $posts->map(fn($p) => $p->meta['seo_score'] ?? null)
            ->filter()
            ->values();

        $avgScore = $scores->count() ? round($scores->avg(), 1) : null;

        // 키워드 클러스터용 데이터
        $keywordStats = $posts->groupBy('keyword')
            ->map(fn($g) => [
                'count' => $g->count(),
                'avg_score' => round(
                    $g->map(fn($p) => $p->meta['seo_score'] ?? 0)->avg(),
                    1
                )
            ]);

        // AI 분석
        $prompt = "
            너는 SEO 전문가이다.
            아래의 프로젝트 데이터를 기반으로 ‘SEO 종합 분석’을 작성하라.

            프로젝트: {$project->name}
            평균 SEO 점수: {$avgScore}

            글 목록:
            " . $posts->map(fn($p) =>
                "제목: {$p->title}, 키워드: {$p->keyword}, SEO: " . ($p->meta['seo_score'] ?? 'N/A')
            )->join("\n") . "

            아래 항목을 한국어로 분석해라:
            1) SEO 전반 요약
            2) 잘한 점 5가지
            3) 부족한 점 5가지
            4) 개선 전략 5가지
            5) 키워드 그룹별 전략
            6) 결론
        ";

        $aiReport = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO expert.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return view('projects.seo_dashboard', compact(
            'project',
            'posts',
            'avgScore',
            'keywordStats',
            'aiReport'
        ));
    }
}
