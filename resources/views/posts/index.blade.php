@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- ----------------------------------------
         상단 타이틀 + 새 글 생성 버튼
    ----------------------------------------- --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">전체 게시글</h1>

        <a href="{{ route('generate.page') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
            + 새 글 생성
        </a>
    </div>

    {{-- ----------------------------------------
         통계 카드
    ----------------------------------------- --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">

        {{-- 총 게시글 --}}
        <div class="p-4 bg-white border rounded shadow">
            <h3 class="text-gray-600 text-sm">총 게시글</h3>
            <p class="text-2xl font-bold">{{ $posts->count() }}</p>
        </div>

        {{-- 평균 CTR --}}
        <div class="p-4 bg-white border rounded shadow">
            <h3 class="text-gray-600 text-sm">평균 CTR</h3>
            <p class="text-2xl font-bold">
                {{ number_format($posts->avg('ctr') ?? 0, 2) }}%
            </p>
        </div>

        {{-- 평균 조회수 --}}
        <div class="p-4 bg-white border rounded shadow">
            <h3 class="text-gray-600 text-sm">평균 조회수</h3>
            <p class="text-2xl font-bold">
                {{ number_format($posts->avg('views') ?? 0) }}
            </p>
        </div>

        {{-- 최근 7일 신규 게시글 --}}
        <div class="p-4 bg-white border rounded shadow">
            <h3 class="text-gray-600 text-sm">최근 7일 신규 게시글</h3>
            <p class="text-2xl font-bold">
                {{ $posts->where('created_at', '>=', now()->subDays(7))->count() }}
            </p>
        </div>

    </div>

    {{-- ----------------------------------------
         검색/필터 영역
    ----------------------------------------- --}}
    <form method="GET" action="/posts" class="mb-10">
        <div class="flex flex-wrap items-end gap-4">

            {{-- 프로젝트 필터 --}}
            <div>
                <label class="font-semibold">프로젝트</label>
                <select name="project_id" class="border rounded p-2">
                    <option value="">전체</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}"
                            @if(request('project_id') == $prj->id) selected @endif>
                            {{ $prj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- 검색 --}}
            <div>
                <label class="font-semibold">검색</label>
                <input type="text" name="q" class="border rounded p-2 w-60"
                       placeholder="제목 또는 키워드 검색"
                       value="{{ request('q') }}">
            </div>

            <button class="px-4 py-2 bg-gray-800 text-white rounded">
                적용
            </button>
        </div>
    </form>

    {{-- 게시글이 없을 때 --}}
    @if ($posts->count() === 0)
        <div class="text-center text-gray-500 py-20">
            게시글이 없습니다.
        </div>
    @endif

    {{-- ----------------------------------------
         게시글 리스트
    ----------------------------------------- --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($posts as $post)
            <a href="{{ route('posts.show', $post->id) }}"
               class="block p-5 bg-white border rounded-xl shadow hover:shadow-lg transition">

                {{-- 제목 --}}
                <h2 class="text-xl font-bold mb-2">
                    {{ $post->title }}
                </h2>

                {{-- 프로젝트명 --}}
                <p class="text-gray-500 text-sm mb-1">
                    프로젝트: <strong>{{ $post->project->name ?? '-' }}</strong>
                </p>

                {{-- 키워드 --}}
                <p class="text-gray-500 text-sm mb-3">
                    키워드: <strong>{{ $post->keyword }}</strong>
                </p>

                {{-- 통계 --}}
                <div class="grid grid-cols-3 text-center text-sm py-3 bg-gray-50 rounded-lg">
                    <div>
                        <strong>{{ $post->views ?? 0 }}</strong><br>
                        <span class="text-gray-500">조회수</span>
                    </div>
                    <div>
                        <strong>{{ $post->clicks ?? 0 }}</strong><br>
                        <span class="text-gray-500">클릭수</span>
                    </div>
                    <div>
                        <strong>{{ number_format($post->ctr ?? 0, 2) }}%</strong><br>
                        <span class="text-gray-500">CTR</span>
                    </div>
                </div>

                {{-- 생성일 --}}
                <p class="text-gray-400 text-xs mt-3">
                    {{ $post->created_at->format('Y-m-d H:i') }}
                </p>

            </a>
        @endforeach

    </div>

</div>
@endsection
