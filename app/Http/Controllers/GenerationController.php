<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CodexMaxService;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenerationController extends Controller
{
    protected CodexMaxService $ai;

    public function __construct(CodexMaxService $ai)
    {
        // 🔹 로컬 LLM(Qwen 7B 등)과 연결된 서비스
        $this->ai = $ai;
    }

    /* --------------------------------
        /generate 페이지 렌더링
    --------------------------------- */
    public function generatePage()
    {
        $projects = Project::where('user_id', Auth::id())->get();

        return view('generate', compact('projects'));
    }

    /* --------------------------------
        스타일 프리셋
    --------------------------------- */
    private function getStyleText($style)
    {
        return match ($style) {
            'emotional'    => "감성적이고 공감되는 문체로 작성해주세요.",
            'professional' => "전문적이고 신뢰감을 주는 문체로 작성해주세요.",
            'casual'       => "친근하고 캐주얼한 블로그 스타일로 작성해주세요.",
            'short'        => "짧고 간결하게 핵심 위주로 작성해주세요.",
            'seo'          => "SEO 최적화 방식으로 작성하고 주요 키워드를 자연스럽게 포함해주세요.",
            default        => "",
        };
    }

    /* --------------------------------
        1) 제목 생성
    --------------------------------- */
    public function generateTitles(Request $request)
    {
        $keyword   = $request->keyword;
        $style     = $request->style ?? 'default';
        $styleText = $this->getStyleText($style);

        $prompt = "
            너는 한국어 블로그 SEO 전문가야.
            아래 키워드를 기반으로 클릭 잘 나오는 제목 5개를 5줄로만 출력해.

            키워드: {$keyword}
            스타일: {$styleText}

            규칙:
            - 각 줄에 제목만 (5줄)
            - 번호, JSON, 설명 금지
        ";

        $raw = $this->ai->chat($prompt);
        Log::info('TITLE RAW', ['raw'=>$raw]);

        $lines = preg_split("/\r\n|\n|\r/", trim($raw));

        $titles = array_slice(array_map('trim', $lines), 0, 5);

        while (count($titles) < 5) {
            $titles[] = $keyword . " 자동화 가이드";
        }

        return response()->json(['titles' => $titles]);
    }


    /* --------------------------------
        2) 본문 생성
    --------------------------------- */
    public function generateContent(Request $request)
    {
        $keyword   = $request->keyword ?? '';
        $title     = $request->title ?? '';
        $style     = $request->style ?? 'default';
        $styleText = $this->getStyleText($style);

        $prompt = "
            아래 조건에 맞춰 블로그 본문을 HTML로 작성해줘.

            키워드: {$keyword}
            제목: {$title}
            스타일: {$styleText}

            출력 형식:
            - 먼저 본문 HTML만 작성 (p, h2, h3, ul/li 등을 적절히 사용)
            - 그 다음 줄에 'META:' 라고 쓰고
              메타 설명 문장 한 줄을 이어서 작성

            예시 출력 형태:
            <h1>...</h1><p>...</p>...
            META: 이 글은 ~~ 에 대한 설명입니다.
        ";

        try {
            $raw = $this->ai->chat($prompt);
        } catch (\Throwable $e) {
            Log::error('CONTENT ERROR', [
                'userId' => Auth::id(),
                'msg'    => $e->getMessage(),
            ]);

            return response()->json([
                'title' => $title,
                'html'  => '<p>로컬 LLM 호출 중 오류가 발생했습니다.</p>',
                'meta'  => ['description' => '로컬 LLM 호출 실패'],
            ]);
        }

        // META 분리
        $html = $raw;
        $meta = ['description' => ''];

        if (str_contains($raw, 'META:')) {
            [$htmlPart, $metaPart] = explode('META:', $raw, 2);
            $html = trim($htmlPart);
            $meta['description'] = trim($metaPart);
        }

        return response()->json([
            'title' => $title,
            'html'  => $html ?: '<p>생성 오류가 발생했습니다.</p>',
            'meta'  => $meta,
        ]);
    }

    /* --------------------------------
        3) SEO 분석 (Generate 페이지용)
    --------------------------------- */
    public function analyzeSEO(Request $request)
    {
        $title   = $request->title ?? '';
        $html    = $request->html ?? '';
        $keyword = $request->keyword ?? '';

        $prompt = "
            너는 SEO 분석 전문가다.

            아래 콘텐츠의 SEO 품질을 분석하고,
            항목별 요약을 한국어로 작성해줘.

            --- 콘텐츠 정보 ---
            제목: {$title}
            키워드: {$keyword}
            본문 HTML:
            {$html}

            출력 형식:
            - SEO 점수 (0~100)
            - 가독성 평가
            - 키워드 사용 평가
            - 구조(H1/H2/문단수) 요약
            - 주요 문제점 3~5개
            - 개선 제안 3~5개

            사람이 읽기 좋은 형식의 텍스트로만 출력해줘.
        ";

        try {
            $raw = $this->ai->chat($prompt);
        } catch (\Throwable $e) {
            Log::error('ANALYZE ERROR', [
                'userId' => Auth::id(),
                'msg'    => $e->getMessage(),
            ]);

            $raw = "로컬 LLM 호출 중 오류가 발생했습니다.\n에러 메시지: " . $e->getMessage();
        }

        return response()->json([
            'result' => $raw,
        ]);
    }

    /* --------------------------------
        4) 태그 자동 생성
    --------------------------------- */
    public function generateTags(Request $request)
    {
        $title   = $request->title ?? '';
        $keyword = $request->keyword ?? '';
        $html    = $request->html ?? '';

        $prompt = "
            아래 블로그 글의 제목, 키워드, 본문을 기반으로
            한국 블로그(네이버/티스토리) 기준 SEO 최적화 태그 10~15개를 만들어줘.

            조건:
            - 짧고 간결하게
            - 겹치는 태그 제거
            - 검색량 높은 단어 중심
            - 한 줄에 하나씩 태그만 출력 (설명 X)

            제목: {$title}
            키워드: {$keyword}
            본문 일부: " . mb_substr(strip_tags($html), 0, 700) . "
        ";

        try {
            $raw = $this->ai->chat($prompt);
        } catch (\Throwable $e) {
            Log::error('TAGS ERROR', [
                'userId' => Auth::id(),
                'msg'    => $e->getMessage(),
            ]);

            // 태그가 아예 없으면 UI가 이상해지니까 최소 3개 정도 기본값
            $fallback = $keyword !== '' ? [$keyword, "{$keyword} 블로그", "{$keyword} 정보"] : ['블로그', '정보', '자동생성'];
            return response()->json(['tags' => $fallback]);
        }

        $tags = $this->parseLines($raw);

        return response()->json(['tags' => $tags]);
    }

    /* --------------------------------
        5) 썸네일 생성 (프롬프트 기반)
    --------------------------------- */
    public function generateThumbnail(Request $request)
    {
        $title = $request->title ?? '';
        $html  = $request->html ?? '';

        $prompt = "
            아래 블로그 글에 어울리는 썸네일 이미지를 설명하는 영어 한 문장 프롬프트를 만들어줘.

            제목: {$title}
            본문 일부: " . mb_substr(strip_tags($html), 0, 300) . "

            규칙:
            - 오직 영어 한 문장만 출력
            - 설명, 번역, 다른 텍스트 금지
        ";

        try {
            $desc = trim($this->ai->chat($prompt));
        } catch (\Throwable $e) {
            Log::error('THUMB ERROR', [
                'userId' => Auth::id(),
                'msg'    => $e->getMessage(),
            ]);
            $desc = 'a simple thumbnail image for a blog post';
        }

        $fakeUrl = 'https://placehold.co/640x360?text=' . urlencode($title !== '' ? $title : 'Thumbnail');

        return response()->json([
            'thumbnail' => $fakeUrl,
            'prompt'    => $desc,
        ]);
    }

    /* --------------------------------
        6) Draft 저장
    --------------------------------- */
    public function saveDraft(Request $request)
    {
        $payload = $request->all();

        $post = Post::create([
            'user_id'      => Auth::id(),
            'project_id'   => $payload['project_id'] ?? null,
            'keyword'      => $payload['keyword'] ?? '',
            'title'        => $payload['title'] ?? '',
            'html'         => $payload['html'] ?? '',
            'content'      => strip_tags($payload['html'] ?? ''),
            'meta'         => null,
            'is_draft'     => true,
            'generated_at' => now(),
        ]);

        return response()->json([
            'result' => true,
            'post'   => $post,
        ]);
    }

    /* --------------------------------
        7) 기존 글 저장(수정용)
    --------------------------------- */
    public function savePost(Request $req)
    {
        $post = Post::findOrFail($req->id);

        $meta = $post->meta ?? [];
        if ($req->tags) {
            $meta['tags'] = $req->tags;
        }

        $post->update([
            'title'   => $req->title ?? $post->title,
            'keyword' => $req->keyword ?? $post->keyword,
            'html'    => $req->html ?? $post->html,
            'content' => strip_tags($req->html ?? $post->html),
            'meta'    => $meta,
        ]);

        return response()->json(['ok' => true]);
    }

    /* --------------------------------
        8) 내부 링크 추천
    --------------------------------- */
    public function recommendInternalLinks(Request $request)
    {
        $project_id     = $request->project_id;
        $currentContent = $request->html ?? '';
        $currentKeyword = $request->keyword ?? '';

        $posts = Post::where('project_id', $project_id)
            ->where('id', '!=', $request->post_id)
            ->get();

        $prompt = "
            너는 SEO 내부 링크 추천 엔진이다.

            현재 글 키워드: {$currentKeyword}
            현재 글 내용 일부: " . mb_substr(strip_tags($currentContent), 0, 500) . "

            아래는 같은 프로젝트의 다른 글 목록이다.
            이 중에서 내부링크로 연결하기 좋은 글 5~10개를 골라라.

            출력 형식:
            - JSON 필요 없음
            - 각 줄에 'ID - 제목 (키워드)' 형식으로 출력

            다른 글 목록(JSON):
            " . $posts->map(fn ($p) => [
                'id'      => $p->id,
                'title'   => $p->title,
                'keyword' => $p->keyword,
            ])->toJson(JSON_UNESCAPED_UNICODE) . "
        ";

        try {
            $raw = $this->ai->chat($prompt);
        } catch (\Throwable $e) {
            Log::error('INTERNAL LINK ERROR', [
                'userId' => Auth::id(),
                'msg'    => $e->getMessage(),
            ]);

            return response()->json(['links' => []]);
        }

        $lines = $this->parseLines($raw);
        $links = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s*-\s*(.+?)\s*\((.*?)\)\s*$/u', $line, $m)) {
                $links[] = [
                    'id'      => (int) $m[1],
                    'title'   => $m[2],
                    'keyword' => $m[3],
                ];
            }
        }

        return response()->json([
            'links' => $links,
        ]);
    }

    /* --------------------------------
        공통: 라인 파서 (번호 제거)
    --------------------------------- */
    private function parseLines(?string $text): array
    {
        if (!$text || !is_string($text)) {
            return [];
        }

        $lines = preg_split("/\r\n|\n|\r/", $text);

        $lines = array_map(function ($line) {
            // 앞의 번호/불릿 제거: "1. ", "2) ", "- " 등
            $line = preg_replace('/^\s*[\-\*\d]+[\.\)]?\s*/u', '', $line);
            return trim($line);
        }, $lines);

        // 빈 줄 제거
        $lines = array_values(array_filter($lines, fn ($l) => $l !== ''));

        return $lines;
    }

    /* --------------------------------
        JSON Safe 파서 (현재는 태그/제목에 안 씀)
        - 필요 시 재활용용으로 남겨둠
    --------------------------------- */
    private function safeJsonArray($text)
    {
        if (!$text || !is_string($text)) {
            return [];
        }

        $original = $text;

        // 코드블록 제거
        $text = preg_replace('/```(json)?/i', '', $text);
        $text = str_replace('```', '', $text);
        $text = trim($text);

        // 배열만 추출
        if (!str_starts_with($text, '[')) {
            $start = strpos($text, '[');
            $end   = strrpos($text, ']');

            if ($start !== false && $end !== false && $end > $start) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }

        $json = json_decode($text, true);

        if (is_array($json)) {
            return $json;
        }

        Log::warning('safeJsonArray JSON parse failed, fallback to lines', [
            'raw' => mb_substr($original, 0, 500),
        ]);

        return $this->parseLines($original);
    }

    private function safeJsonObject($text)
    {
        if (!$text || !is_string($text)) {
            return [];
        }

        $original = $text;

        // 코드블록 제거
        $text = preg_replace('/```(json)?/i', '', $text);
        $text = str_replace('```', '', $text);
        $text = trim($text);

        if (!str_starts_with($text, '{')) {
            $start = strpos($text, '{');
            $end   = strrpos($text, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }

        $json = json_decode($text, true);

        if (!is_array($json)) {
            Log::warning('safeJsonObject JSON parse failed', [
                'raw' => mb_substr($original, 0, 500),
            ]);
            return [];
        }

        return $json;
    }
}
