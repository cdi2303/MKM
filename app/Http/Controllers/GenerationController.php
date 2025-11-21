<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\OpenAIService;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class GenerationController extends Controller {
    protected $ai;

    public function __construct(OpenAIService $ai){
        $this->ai = $ai;
    }

    /* ----------------------------------------------
        스타일 프리셋 텍스트 구성 함수
    ---------------------------------------------- */
    private function getStyleText($style)
    {
        return match($style) {
            'emotional'    => "감성적이고 공감되는 문체로 작성해주세요.",
            'professional' => "전문적이고 신뢰감을 주는 문체로 작성해주세요.",
            'casual'       => "친근하고 캐주얼한 블로그 스타일로 작성해주세요.",
            'short'        => "짧고 간결하게 핵심 위주로 작성해주세요.",
            'seo'          => "SEO 최적화 방식으로 작성하고 주요 키워드를 자연스럽게 포함해주세요.",
            default        => "",
        };
    }

    /* ----------------------------------------------
        제목 생성
    ---------------------------------------------- */
    public function generateTitles(Request $request)
{
    $keyword = $request->keyword;
    $style   = $request->style ?? 'default';
    $styleText = $this->getStyleText($style);

    // OpenAI에 JSON 배열로만 출력하도록 강력히 명령
    $prompt = "
        아래 규칙을 반드시 지켜줘:

        1) 출력은 반드시 JSON 배열 형식만 사용 (ex: [\"제목1\", \"제목2\", ...])
        2) 설명/코드블록/텍스트 말고 JSON만 출력
        3) 각 제목은 문자열 형태

        키워드: {$keyword}
        스타일: {$styleText}
        위 조건을 따른 제목 5개를 JSON 배열로 출력해줘.
    ";

    $raw = $this->ai->generate([
        'messages' => [
            ['role' => 'system', 'content' => 'You are a professional blog title generator.'],
            ['role' => 'user', 'content' => $prompt]
        ]
    ]);

    // ---- JSON 안전 파서 적용 ----
    $titles = $this->safeJsonArray($raw);

    return response()->json(['titles' => $titles]);
}


    /* ----------------------------------------------
        본문 생성
    ---------------------------------------------- */
    public function generateContent(Request $request)
    {
        $keyword = $request->keyword;
        $title   = $request->title;
        $style   = $request->style ?? 'default';
        $styleText = $this->getStyleText($style);

        // GPT에게 JSON으로 명확하게 출력하도록 강제
        $prompt = "
            아래 규칙을 반드시 지켜서 출력하세요:

            1) 출력은 반드시 JSON 형식이어야 함.
            2) JSON 구조는 아래와 같아야 함:
            {
                \"html\": \"<h1>...</h1><p>...</p>\",
                \"meta\": {
                    \"description\": \"...\"
                }
            }
            3) 설명, 코드블록, 여분의 텍스트 절대 금지.
            4) html 안에는 순수 HTML만 포함.

            ---- 생성 조건 ----
            키워드: {$keyword}
            제목: {$title}
            스타일: {$styleText}

            위 조건으로 SEO 최적화 블로그 본문을 HTML로 생성하세요.
        ";

        $raw = $this->ai->generateHTML([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional SEO blog writer.'],
                ['role' => 'user', 'content' => $prompt],
            ]
        ]);

        // JSON 안전 파서 적용
        $json = $this->safeJsonObject($raw);

        // 파싱 실패한 경우 대비
        $html = $json['html'] ?? '<p>생성 오류가 발생했습니다.</p>';
        $meta = $json['meta'] ?? ['description' => ''];

        return response()->json([
            'title' => $title,
            'html'  => $html,
            'meta'  => $meta
        ]);
    }


    /* ----------------------------------------------
        저장 전용 API
    ---------------------------------------------- */
    public function savePost(Request $request){
        $post = Post::create([
            'user_id'=>Auth::id(),
            'project_id'=>$request->project_id,
            'keyword'=>$request->keyword,
            'title'=>$request->title,
            'html'=>$request->html,
            'content'=>strip_tags($request->html),
            'meta'=>['description'=>mb_substr(strip_tags($request->html),0,150)],
            'generated_at'=>now()
        ]);

        $this->saveVersion($post);

        return response()->json(['result'=>true, 'post'=>$post]);
    }

    public function analyzeSEO(Request $request)
    {
        $title = $request->title;
        $html  = $request->html;
        $keyword = $request->keyword ?? '';

        $prompt = "
            당신은 SEO 분석 전문가입니다.

            아래 콘텐츠의 SEO 품질을 분석하고 반드시 JSON으로만 출력하세요.

            --- 콘텐츠 정보 ---
            제목: {$title}
            키워드: {$keyword}
            본문 HTML:
            {$html}

            --- 출력 형식(JSON) ---
            {
                \"score\": number (0~100),
                \"readability\": \"텍스트\",
                \"keyword_usage\": \"텍스트\",
                \"structure\": {
                    \"h1\": number,
                    \"h2\": number,
                    \"paragraphs\": number
                },
                \"problems\": [\"문제1\", \"문제2\"],
                \"suggestions\": [\"개선안1\", \"개선안2\"]
            }

            절대로 설명 없이 JSON만 출력하세요.
        ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO auditor.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $json = $this->safeJsonObject($raw);

        return response()->json($json);
    }

    public function saveDraft(Request $request)
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'keyword' => $request->keyword,
            'title' => $request->title,
            'html' => $request->html,
            'content' => strip_tags($request->html),
            'meta' => null,
            'is_draft' => true,
            'generated_at' => now(),
        ]);

        return response()->json(['result' => true, 'post' => $post]);
    }

    public function generatePage()
    {
        $projects = \App\Models\Project::where('user_id', Auth::id())->get();
        return view('generate', compact('projects'));
    }

    private function saveVersion($post)
    {
        $latest = \App\Models\PostVersion::where('post_id', $post->id)
            ->orderBy('version', 'desc')
            ->first();

        $versionNumber = $latest ? $latest->version + 1 : 1;

        \App\Models\PostVersion::create([
            'post_id' => $post->id,
            'version' => $versionNumber,
            'title'   => $post->title,
            'keyword' => $post->keyword,
            'html'    => $post->html,
            'content' => $post->content,
            'meta'    => $post->meta,
        ]);
    }

    private function safeJsonArray($text)
    {
        // 코드블록 제거
        $text = preg_replace('/```(json)?/i', '', $text);
        $text = str_replace('```', '', $text);

        // 앞뒤 공백 제거
        $text = trim($text);

        // JSON 배열만 추출 (가장 안전한 방법)
        if (!str_starts_with($text, '[')) {
            $start = strpos($text, '[');
            $end   = strrpos($text, ']');
            if ($start !== false && $end !== false) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }

        // JSON 파싱
        $json = json_decode($text, true);

        // 파싱 실패 → fallback
        if (!is_array($json)) {
            return [];
        }

        return $json;
    }

    private function safeJsonObject($text)
    {
        // 코드블록 제거
        $text = preg_replace('/```(json)?/i', '', $text);
        $text = str_replace('```', '', $text);

        $text = trim($text);

        // JSON 객체만 추출
        if (!str_starts_with($text, '{')) {
            $start = strpos($text, '{');
            $end   = strrpos($text, '}');
            if ($start !== false && $end !== false) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }

        $json = json_decode($text, true);

        if (!is_array($json)) {
            return [];
        }

        return $json;
    }
}
