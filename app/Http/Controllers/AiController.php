<?php

namespace App\Http\Controllers;

use App\Services\CodexMaxService;

class AiController extends Controller
{
    protected CodexMaxService $ai;

    public function __construct(CodexMaxService $ai)
    {
        $this->ai = $ai;
    }

    public function seoAnalyze(Request $request)
    {
        $prompt = "
    제목: {$request->title}
    본문: {$request->html}
    키워드: {$request->keyword}

    위 글의 SEO 점수, 가독성 평가, 개선안 5개를 정리해줘.
    ";

        $result = $this->ai->chat($prompt);

        return response()->json(['result' => $result]);
    }

    public function test(CodexMaxService $ai)
    {
        $response = $ai->chat("안녕, 너 작동하니?");
        return response()->json($response);
    }
}
