<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenAIService;

class ProjectClusterController extends Controller
{
    protected $ai;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    // 클러스터 화면 보여주기
    public function view($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('projects.cluster', compact('project'));
    }

    // 클러스터 데이터 생성 (AI 호출)
    public function generate($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // 프로젝트 내 글 가져오기
        $posts = $project->posts()->where('is_draft', false)->get();

        // AI에게 유사도 분석 요청
        $prompt = "
            너는 SEO 콘텐츠 분석 엔진이다.

            아래는 프로젝트의 글이다.
            각 글의 ID, 키워드, 내용 일부를 보고
            '글 간 유사도 네트워크'를 생성하라.

            출력은 반드시 다음 JSON 구조만 사용하라:

            {
                \"nodes\": [
                    {\"id\": 1, \"keyword\": \"키워드\"},
                    ...
                ],
                \"links\": [
                    {\"source\": 1, \"target\": 2, \"score\": 0.8},
                    ...
                ]
            }
        ";

        $postsJson = $posts->map(function ($p) {
            return [
                'id' => $p->id,
                'keyword' => $p->keyword,
                'content' => mb_substr(strip_tags($p->content), 0, 600)
            ];
        });

        $prompt .= "\n\n글 목록:\n" . $postsJson->toJson(JSON_UNESCAPED_UNICODE);

        $response = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a content clustering engine.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        // JSON 안전 파싱
        $json = json_decode($response, true);
        if (!$json) {
            return response()->json([
                'nodes' => [],
                'links' => []
            ]);
        }

        return response()->json($json);
    }
}
