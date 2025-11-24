@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-6">게시글 목록</h1>

        {{-- 프로젝트 필터 --}}
        <form method="GET" class="mb-6">
            <select name="project_id"
                    onchange="this.form.submit()"
                    class="border rounded p-2">
                <option value="">전체 프로젝트</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}"
                            @if(request('project_id') == $p->id) selected @endif>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- 게시글 카드 --}}
        @if($posts->count() === 0)
            <p class="text-gray-500">게시글이 없습니다.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @foreach($posts as $post)
                    <a href="/posts/{{ $post->id }}"
                       class="block bg-white p-4 border rounded-xl shadow hover:shadow-lg transition">

                        {{-- 썸네일 --}}
                        @if($post->thumbnail_url)
                            <img src="{{ $post->thumbnail_url }}"
                                 class="w-full h-40 object-cover rounded mb-3">
                        @else
                            <div class="w-full h-40 bg-gray-200 rounded mb-3 flex items-center justify-center text-gray-500">
                                No Thumbnail
                            </div>
                        @endif

                        {{-- 제목 --}}
                        <h2 class="text-xl font-bold mb-1">{{ Str::limit($post->title, 40) }}</h2>

                        {{-- 키워드 --}}
                        <div class="text-sm text-blue-600 mb-2">
                            키워드: {{ $post->keyword }}
                        </div>

                        {{-- 프로젝트 --}}
                        <div class="text-gray-500 text-sm">
                            프로젝트: {{ $post->project->name ?? '-' }}
                        </div>

                        {{-- 생성일 --}}
                        <div class="text-gray-400 text-xs mt-1">
                            {{ $post->created_at->format('Y-m-d H:i') }}
                        </div>

                        {{-- SEO 점수 --}}
                        @if(isset($post->meta['seo_score']))
                            <div class="mt-2 text-sm">
                                SEO 점수:
                                <span class="font-bold">{{ $post->meta['seo_score'] }}</span>
                            </div>
                        @endif

                        {{-- 버전 수 --}}
                        <div class="mt-1 text-sm text-gray-500">
                            버전: {{ $post->versions->count() }}개
                        </div>
                    </a>
                @endforeach

            </div>
        @endif

    </div>
@endsection
