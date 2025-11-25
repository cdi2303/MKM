@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- Flash 메시지 --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 border text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- 제목 + 삭제/수정 버튼 --}}
    <div class="flex items-start justify-between mb-4">

        <h1 class="text-3xl font-bold">{{ $post->title }}</h1>

        <div class="flex gap-3">

            {{-- 삭제 버튼 --}}
            <form action="{{ route('posts.destroy', $post->id) }}"
                method="POST"
                onsubmit="return confirm('정말 삭제하시겠습니까?');">
                @csrf
                @method('DELETE')
                <button class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    삭제
                </button>
            </form>

            {{-- 수정 버튼 --}}
            <a href="{{ route('posts.edit', $post->id) }}"
               class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">
                수정
            </a>
        </div>

    </div>

    {{-- 프로젝트/키워드 --}}
    <p class="text-gray-500 text-sm mb-3">
        프로젝트: <strong>{{ $post->project->name ?? '-' }}</strong> |
        키워드: <strong>{{ $post->keyword }}</strong>
    </p>

    {{-- 썸네일 --}}
    @if($post->thumbnail_url)
        <img src="{{ $post->thumbnail_url }}"
            class="w-full max-w-xl rounded mb-6 shadow">
    @endif

    {{-- AI 버튼들 --}}
    <div class="flex flex-wrap gap-3 mb-6">

        <button id="seoAnalyzeBtn" class="px-4 py-2 bg-purple-600 text-white rounded">
            SEO 분석하기
        </button>

        <button id="upgradeContentBtn" class="px-4 py-2 bg-green-600 text-white rounded">
            SEO 자동 개선하기
        </button>

        <button id="generateTagsBtn" class="px-4 py-2 bg-indigo-600 text-white rounded">
            🔖 자동 태그 생성하기
        </button>

        <button id="internalLinkBtn" class="px-4 py-2 bg-yellow-600 text-white rounded">
            🔗 내부 링크 추천하기
        </button>

        <button onclick="generateABTitles()" class="px-4 py-2 bg-blue-700 text-white rounded">
            제목 AB 테스트 생성
        </button>

        <button id="qualityCheckBtn"
                class="px-4 py-2 bg-red-600 text-white rounded">
            🧪 콘텐츠 품질 진단
        </button>

        <a href="/posts/{{ $post->id }}/versions"
            class="px-4 py-2 bg-gray-700 text-white rounded">
            버전 히스토리 보기
        </a>
    </div>

    {{-- SEO wynik --}}
    <div id="seoResult" class="mt-6 hidden bg-white p-4 rounded shadow"></div>

    {{-- 본문 --}}
    <div class="prose max-w-none bg-white p-4 border rounded shadow">
        {!! $post->html !!}
    </div>

    {{-- 내부 링크 --}}
    <div id="internalLinkBox" class="mt-10 hidden bg-white p-4 border rounded shadow">
        <h3 class="text-xl font-bold mb-3">내부 링크 추천 결과</h3>
        <ul id="internalLinkList" class="list-disc ml-5"></ul>
    </div>

    {{-- Meta --}}
    @if(isset($post->meta['description']) || isset($post->meta['seo_score']))
        <div class="mt-8 p-4 bg-gray-50 rounded border">
            <h3 class="text-xl font-bold mb-2">메타 정보</h3>

            @if(isset($post->meta['seo_score']))
                <p class="mb-2">SEO 점수:
                    <strong>{{ $post->meta['seo_score'] }}</strong>
                </p>
            @endif

            @if(isset($post->meta['description']))
                <p class="text-gray-600">
                    <strong>Description:</strong> {{ $post->meta['description'] }}
                </p>
            @endif
        </div>
    @endif

    <div id="qualityBox" class="mt-6 hidden bg-white p-4 rounded shadow"></div>

    {{-- 통계 --}}
    <div class="mt-6 p-4 bg-gray-50 border rounded">
        <h2 class="text-xl font-bold mb-2">📈 포스트 통계</h2>

        <p><strong>조회수:</strong> {{ $post->views ?? 0 }}</p>
        <p><strong>클릭수:</strong> {{ $post->clicks ?? 0 }}</p>
        <p><strong>CTR:</strong> {{ number_format($post->ctr ?? 0, 2) }}%</p>
        <p><strong>좋아요:</strong> {{ $post->likes ?? 0 }}</p>
        <p><strong>댓글수:</strong> {{ $post->comments ?? 0 }}</p>
        <p class="text-gray-500 text-sm">최근 동기화: {{ $post->last_synced_at ?? '—' }}</p>
    </div>

    {{-- 태그 --}}
    <hr class="my-8">

    <div class="mt-6">
        <h3 class="text-xl font-bold mb-2">태그</h3>

        @if(isset($post->meta['tags']) && count($post->meta['tags']) > 0)
            <div id="tagList" class="flex flex-wrap gap-2 mb-3">
                @foreach($post->meta['tags'] as $tag)
                    <span class="px-3 py-1 bg-gray-200 rounded-full text-sm">{{ $tag }}</span>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 mb-3">등록된 태그가 없습니다.</p>
            <div id="tagList" class="flex flex-wrap gap-2 mb-3"></div>
        @endif
    </div>

    {{-- 워드프레스 업로드 --}}
    <div class="mb-6 flex gap-3 mt-10">
        <form method="POST" action="/posts/{{ $post->id }}/publish/wordpress">
            @csrf
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                워드프레스 업로드
            </button>
        </form>
    </div>

    {{-- CTR 차트 --}}
    <div class="mt-6 p-4 bg-white border rounded">
        <h2 class="text-xl font-bold mb-2">CTR 시각화</h2>
        <canvas id="ctrChart" width="300" height="300"></canvas>
    </div>

</div>
<script>
window.CSRF = "{{ csrf_token() }}";

window.POST_DATA = {
    id: {{ $post->id }},
    title: @json($post->title),
    html: @json($post->html),
    keyword: @json($post->keyword),
    project_id: {{ $post->project_id }},
    ctr: {{ $post->ctr ?? 0 }},
    generateTitleUrl: "/posts/{{ $post->id }}/generate-title-tests"
};

window.ROUTES = {
    generateAnalyze: "{{ route('generate.analyze') }}",
    generateUpgrade: "{{ route('generate.upgrade') }}",
    generateTags: "{{ route('generate.tags') }}",
    savePost: "{{ route('generate.savePost') }}",
    generateInternalLinks: "{{ route('generate.internalLinks') }}",
    qualityCheck: "{{ route('generate.qualityCheck') }}"
};
</script>

<script src="/js/posts/show.js"></script>

@endsection
