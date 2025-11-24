<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenAIService;

class PostQualityController extends Controller
{
    protected $ai;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    // 분석 화면
    public function index($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('posts.quality', compact('post'));
    }

    // 품질 분석 + 점수 산출
    public function analyze($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $prompt = "
            당신은 최고급 블로그 콘텐츠 분석가입니다.

            아래 글을 품질 기준으로 평가하세요.

            --- 콘텐츠 정보 ---
            제목: {$post->title}
            키워드: {$post->keyword}
            본문 HTML:
            {$post->html}

            --- 평가 항목 ---
            1) SEO 점수 (0~100)
            2) 문장 가독성 (0~100)
            3) 정보 밀도 (0~100)
            4) 반복도(중복 패턴) 감점 기반 점수 (0~100)
            5) 키워드 자연스러움 (0~100)

            --- 출력(JSON ONLY) ---
            {
                \"scores\": {
                    \"seo\": ...,
                    \"readability\": ...,
                    \"density\": ...,
                    \"redundancy\": ...,
                    \"keyword\": ...
                },
                \"total\": 0~100 사이 총합 점수,
                \"problems\": [ ... ],
                \"suggestions\": [ ... ]
            }
        ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a content quality analyst.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return response()->json(json_decode($raw, true));
    }

    // 자동 리라이트 기능
    public function rewrite($id)
    {
        $post = Post::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $prompt = "
            다음 글을 자연스럽고 고급스럽게 리라이팅하되,
            SEO에 최적화하고 중복 문장을 제거하고,
            문장 흐름을 매끄럽게 만들어라.

            --- 입력 ---
            제목: {$post->title}
            키워드: {$post->keyword}
            HTML:
            {$post->html}

            --- JSON ONLY 출력 ---
            {
                \"html\": \"...\",
                \"diff\": \"<ul><li>이 부분이 이렇게 개선됨...</li></ul>\"
            }
        ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional content rewriter.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return response()->json(json_decode($raw, true));
    }
}
