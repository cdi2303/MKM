<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\OpenAIService;

class ProjectSeoPdfController extends Controller
{
    protected $ai;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    public function generate($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $posts = $project->posts()->orderBy('id')->get();

        // ------- AI에게 리포트 본문 생성하게 하기 -------
        $prompt = "
            너는 SEO 전문가이며 아래의 프로젝트 내용으로
            ‘SEO 분석 보고서’를 생성하라.

            --- 프로젝트 정보 ---
            프로젝트명: {$project->name}

            --- 포스트 목록 ---
            " . $posts->map(fn($p) =>
                "제목: {$p->title}, 키워드: {$p->keyword}, 점수: " . ($p->meta['seo_score'] ?? 'N/A')
            )->join("\n") . "

            --- 리포트 구성 ---
            1) 프로젝트 SEO 요약 개요
            2) 전체 SEO 성능 평가
            3) 잘한 점 5가지
            4) 문제점 5가지
            5) 향후 개선 전략 5가지
            6) 키워드 클러스터별 분석
            7) 결론 및 운영 전략

            자연스러운 한국어로 상세하게 작성하라.
        ";

        $report = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO expert.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        // PDF HTML 렌더링
        $pdf = Pdf::loadView('projects.seo_pdf', [
            'project' => $project,
            'posts' => $posts,
            'report' => $report
        ]);

        return $pdf->download("SEO_Report_{$project->name}.pdf");
    }
}
