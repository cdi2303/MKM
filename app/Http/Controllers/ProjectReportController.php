<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenAIService;

class ProjectReportController extends Controller
{
    protected $ai;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    public function index($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $posts = $project->posts
            ->where('is_draft', false)
            ->sortByDesc('id')
            ->values();

        // TOP 5 / Bottom 5
        $ranked = $posts->filter(fn($p) => isset($p->meta['seo_score']))
            ->sortByDesc(fn($p) => $p->meta['seo_score']);

        $top5 = $ranked->take(5);
        $bottom5 = $ranked->sortBy(fn($p) => $p->meta['seo_score'])->take(5);

        // 평균 SEO
        $scores = $posts->map(fn($p) => $p->meta['seo_score'] ?? null)
            ->filter()
            ->values();

        $avgScore = $scores->count() ? round($scores->avg(), 1) : null;

        // 프로젝트 전략 AI 요약
        $prompt = "
            너는 SEO 전략 컨설턴트다.
            아래 프로젝트의 글 목록과 SEO 데이터를 기반으로
            '프로젝트 SEO 전략 요약 리포트'를 작성하라.

            프로젝트: {$project->name}
            평균 SEO 점수: {$avgScore}

            글 목록:
            " . $posts->map(fn($p) =>
                "제목: {$p->title}, 키워드: {$p->keyword}, 점수: " . ($p->meta['seo_score'] ?? 'N/A')
            )->join("\n")
            . "

            출력 형식:
            1) 전체 상태 요약
            2) 강점 5가지
            3) 약점 5가지
            4) 개선 전략 5가지
            5) 우선순위 정리 (1~3)
        ";

        $aiSummary = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO strategist.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return view('projects.report', compact(
            'project',
            'posts',
            'top5',
            'bottom5',
            'avgScore',
            'aiSummary'
        ));
    }
}
