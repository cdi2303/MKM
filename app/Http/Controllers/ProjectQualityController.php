<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenAIService;

class ProjectQualityController extends Controller
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

        $posts = $project->posts()
            ->where('is_draft', false)
            ->get();

        // AI에게 품질 점수 생성 요청
        $prompt = "
            너는 콘텐츠 품질 진단 전문가이다.

            아래 프로젝트 데이터를 분석해라:

            " . $posts->map(fn($p) =>
                "제목: {$p->title}
                 키워드: {$p->keyword}
                 내용: " . mb_substr(strip_tags($p->content), 0, 1200)
            )->join("\n---\n") . "

            아래 형식의 JSON으로만 출력하라:

            {
                \"posts\": [
                    {
                        \"title\": \"...\",
                        \"keyword\": \"...\",
                        \"score\": number (0~100),
                        \"problems\": [\"문제1\", \"문제2\", ...],
                        \"suggest\": [\"개선안1\", \"개선안2\", ...]
                    }
                ],
                \"pattern\": {
                    \"top_problems\": [\"...\", \"...\"],
                    \"priority\": [\"...\", \"...\"],
                    \"summary\": \"프로젝트 전체 품질 총평\"
                }
            }

            부정확한 JSON을 출력하지 마라.
        ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a content quality auditor.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $json = json_decode($raw, true);

        return view('projects.quality', [
            'project' => $project,
            'analysis' => $json
        ]);
    }
}
