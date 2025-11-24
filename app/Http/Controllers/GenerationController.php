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
    public function savePost(Request $request)
    {
        $title = $request->title ?? '제목 없음';
        $cleanText = strip_tags($request->html);

        $post = Post::create([
            'user_id'       => Auth::id(),
            'project_id'    => $request->project_id,
            'keyword'       => $request->keyword,
            'title'         => $title,
            'html'          => $request->html,
            'content'       => $cleanText,

            // SEO meta
            'meta'          => [
                'description' => mb_substr($cleanText, 0, 150),
                'tags'        => $request->tags ?? [],
                'seo_score'   => $request->seo_score ?? null
            ],

            // 썸네일 저장 (없으면 null)
            'thumbnail_url' => $request->thumbnail_url ?? null,

            'generated_at'  => now()
        ]);

        // 버전 관리 기능 유지
        $this->saveVersion($post);

        return response()->json([
            'result' => true,
            'post'   => $post
        ]);
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

    public function upgradeContent(Request $request)
    {
        $title   = $request->title;
        $html    = $request->html;
        $keyword = $request->keyword ?? '';

        $prompt = "
            아래 콘텐츠를 SEO 기준에 맞게 자동으로 개선하세요.

            ---- 기존 콘텐츠 ----
            제목: {$title}
            키워드: {$keyword}

            본문 HTML:
            {$html}

            ---- 요구사항 ----
            1) 오직 JSON만 출력하세요.
            2) JSON 구조:
            {
                \"html\": \"<h1>...</h1><p>...</p>\",
                \"changes\": [\"어떤 부분이 왜 개선되었는지 설명\"],
                \"diff\": \"<ul><li>바뀐 사항 요약</li></ul>\"
            }
            3) html 은 순수 HTML 태그로만 구성.
            4) 스타일: 자연스럽고 읽기 쉬운 한국어.
            5) 키워드 남용 금지. 자연스럽게 포함.

            절대로 JSON 외 다른 텍스트를 포함하지 마세요.
        ";

        $raw = $this->ai->generateHTML([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert content optimizer.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $json = $this->safeJsonObject($raw);

        return response()->json($json);
    }

    public function exploreKeyword(Request $request)
    {
        $keyword = $request->keyword;

        $prompt = "
            당신은 SEO 키워드 분석 전문가입니다.

            아래 메인 키워드에 대해 다음 조건으로 분석하세요:

            1) 연관 키워드 10개
            2) 각 키워드의 검색 의도(정보성/구매성)
            3) 예상 경쟁 난이도 점수(0~100)
            4) 롱테일 키워드 10개 추가 생성
            5) 오직 JSON만 출력 (설명 금지)

            출력 JSON 형식:
            {
                \"related\": [
                    {\"keyword\": \"...\", \"intent\": \"정보성\", \"difficulty\": 42},
                    ...
                ],
                \"longtail\": [
                    \"...\",
                    \"...\"
                ]
            }

            메인 키워드: {$keyword}
        ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO keyword research expert.'],
                ['role' => 'user', 'content' => $prompt],
            ]
        ]);

        $json = $this->safeJsonObject($raw);

        return response()->json($json);
    }

    public function recommendInternalLinks(Request $request)
    {
        $project_id = $request->project_id;
        $currentContent = $request->html;
        $currentKeyword = $request->keyword;

        // 프로젝트에 속한 다른 글을 모두 불러옴
        $posts = Post::where('project_id', $project_id)
            ->where('id', '!=', $request->post_id)
            ->get();

        // OpenAI에 보내서 유사도 분석
        $response = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an SEO internal link recommendation engine.'],
                ['role' => 'user', 'content' =>
                    "현재 글 키워드: {$currentKeyword}\n".
                    "현재 글 내용 일부: ".mb_substr(strip_tags($currentContent), 0, 500)."\n\n".
                    "아래는 같은 프로젝트의 다른 글 목록이다. 내부링크로 연결하기 좋은 글을 5~10개 JSON 배열로 반환해라.\n\n".
                    $posts->map(fn($p)=>[
                        'id'=>$p->id,
                        'title'=>$p->title,
                        'keyword'=>$p->keyword
                    ])->toJson(JSON_UNESCAPED_UNICODE)
                ]
            ]
        ]);

        $items = json_decode($response, true);

        return response()->json([
            'links' => $items
        ]);
    }

    public function generateTags(Request $request)
    {
        $keyword = $request->keyword;
        $title   = $request->title;
        $html    = $request->html;

        $response = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are a blog tag generator.'],
                ['role' => 'user', 'content' =>
                    "블로그 글의 제목, 키워드, 본문을 기반으로 SEO 최적화된 태그 10~15개를 JSON 배열로 만들어줘.
                    - 짧고 간결하게
                    - 겹치는 태그 제거
                    - 검색량 높은 단어 중심
                    - 한국 블로그 플랫폼 기준

                    제목: {$title}
                    키워드: {$keyword}
                    본문 일부: " . mb_substr(strip_tags($html), 0, 700)
                ]
            ]
        ]);

        $tags = json_decode($response, true);

        return response()->json([
            'tags' => $tags
        ]);
    }

    public function generateThumbnail(Request $request)
    {
        $title = $request->title;
        $html = $request->html;

        $prompt = "블로그 글에 사용할 고품질 썸네일. 핵심 주제: {$title}. ".
                "현실적인 사진 스타일, 선명한 조명, 16:9 구도에 어울리도록.";

        $thumb = $this->ai->generateSmartThumbnail($prompt);

        return response()->json([
            'thumbnail' => $thumb
        ]);
    }

    public function generateTitleCandidates(Request $request)
    {
        $post = Post::findOrFail($request->post_id);

        $prompt = "
        아래 글의 제목을 AB 테스트 용도로 최적화된 제목 5개 생성해줘.
        글 제목: {$post->title}
        글 키워드: {$post->keyword}

        조건:
        - 클릭율 높은 스타일
        - 너무 자극적 X
        - 모바일 SEO 최적화
        - JSON 배열만 출력
    ";

        $raw = $this->ai->generate([
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert SEO copywriter.'],
                ['role' => 'user', 'content' => $prompt],
            ]
        ]);

        $titles = json_decode($raw, true);

        foreach ($titles as $t) {
            TitleTest::create([
                'post_id' => $post->id,
                'title' => $t,
            ]);
        }

        return response()->json(['result' => true, 'titles' => $titles]);
    }


}
