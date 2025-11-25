<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\CodexMaxService;
use App\Models\Post;
use Illuminate\Http\Request;

class SeoAnalyzeController extends Controller
{
    protected CodexMaxService $ai;

    public function __construct(CodexMaxService $ai)
    {
        $this->ai = $ai;
    }

    /** SEO 분석 */
    public function analyze(Request $req)
    {
        $prompt = "
        다음 글의 SEO 분석을 해주세요.

        제목: {$req->title}
        키워드: {$req->keyword}

        HTML 본문:
        {$req->html}

        항목별로 정리해서 출력:
        - SEO 점수(0~100)
        - 가독성 평가
        - 키워드 최적화
        - 문제점 5개
        - 개선방향 5개
        - 핵심요약 3줄
        ";

        $result = $this->ai->chat($prompt, 2000);

        return response()->json(['result' => $result]);
    }

    /** SEO 자동 개선 */
    public function upgrade(Request $req)
    {
        $post = Post::findOrFail($req->id);

        $prompt = "
        아래 글을 SEO 기준에 따라 향상시켜 주세요.
        - 전체 길이는 유지
        - 자연스러운 문장으로 다듬기
        - 키워드를 과하지 않게 삽입
        - HTML 구조 유지
        - 제목은 개선하되 의미 유지
        - 문단 가독성 강화

        원본 HTML:
        {$post->html}

        개선된 HTML만 출력하세요. 설명 금지.
        ";

        $improved = $this->ai->chat($prompt, 3000);

        /** 즉시 DB 저장 */
        $post->html = $improved;
        $post->meta = array_merge($post->meta ?? [], [
            'seo_score' => $req->seo_score ?? null,
            'updated_by_ai' => now(),
        ]);
        $post->save();

        return response()->json([
            'success' => true,
            'html' => $improved,
            'message' => "SEO 개선된 글이 저장되었습니다."
        ]);
    }
}
