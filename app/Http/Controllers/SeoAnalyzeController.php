<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CodexMaxService;
use App\Models\Post;

class SeoAnalyzeController extends Controller
{
    protected CodexMaxService $ai;

    public function __construct(CodexMaxService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * SEO 자동 분석
     */
    public function analyze(Request $req)
    {
        $title = $req->title;
        $html = $req->html;
        $keyword = $req->keyword;

        $prompt = "
        다음 블로그 글을 SEO 기준에 따라 분석해줘.

        제목: {$title}
        주요 키워드: {$keyword}

        본문 HTML:
        {$html}

        아래 항목으로 구조화해서 출력:
        1) SEO 점수 (0~100)
        2) 가독성 점수
        3) 문장 흐름 평가
        4) 키워드 최적화 분석
        5) 검색엔진 친화도
        6) 문제점 5개
        7) 개선안 5개
        8) 페이지 구조 분석 (H1/H2/H3)
        9) 메타 정보 제안(description)
        ";

        $result = $this->ai->chat($prompt, 1500);

        return response()->json([
            "result" => nl2br($result)
        ]);
    }

    /**
     * SEO 자동 개선
     */
    public function upgrade(Request $req)
    {
        $title = $req->title;
        $html = $req->html;
        $keyword = $req->keyword;

        $prompt = "
        다음 글을 SEO 기준에 맞춰 더 좋은 구조, 가독성, 키워드 밀도로 개선해줘.

        제목: {$title}
        키워드: {$keyword}

        원본 HTML:
        {$html}

        아래 항목으로 결과를 구성해줘:

        1) 개선된 전체 HTML
        2) 변경한 이유 설명
        3) SEO 최적화 포인트
        ";

        $result = $this->ai->chat($prompt, 2500);

        return response()->json([
            "result" => $result
        ]);
    }
}
