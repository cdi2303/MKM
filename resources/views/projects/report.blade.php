@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-10">

        <h1 class="text-3xl font-bold mb-2">
            프로젝트 리포트 — {{ $project->name }}
        </h1>
        <p class="text-gray-500">이 프로젝트의 전체 SEO 상태를 자동으로 분석한 리포트입니다.</p>

        {{-- 상단 요약 --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white p-4 rounded-xl shadow border">
                <h3 class="text-gray-500 text-sm">평균 SEO 점수</h3>
                <p class="text-4xl font-bold mt-2 text-indigo-600">{{ $avgScore ?? '-' }}</p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow border">
                <h3 class="text-gray-500 text-sm">총 게시글</h3>
                <p class="text-4xl font-bold mt-2">{{ $posts->count() }}</p>
            </div>

            <div class="bg-white p-4 rounded-xl shadow border flex items-center">
                <a href="{{ route('projects.seo.pdf', $project->id) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
                    📄 PDF 다운로드
                </a>
            </div>

        </div>


        {{-- AI 분석 --}}
        <div class="bg-white p-6 rounded-xl shadow border leading-7 whitespace-pre-line">
            <h2 class="text-xl font-bold mb-4">AI SEO 전략 요약</h2>
            {!! nl2br(e($aiSummary)) !!}
        </div>


        {{-- TOP5 / Bottom5 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white p-4 rounded-xl shadow border">
                <h2 class="text-lg font-bold mb-3">📈 SEO 상위 5개 글</h2>

                @foreach($top5 as $p)
                    <div class="border-b py-2">
                        <a href="/posts/{{ $p->id }}" class="font-semibold hover:underline">
                            {{ $p->title }}
                        </a>
                        <p class="text-sm text-gray-500">
                            점수: {{ $p->meta['seo_score'] ?? '-' }} | {{ $p->keyword }}
                        </p>
                    </div>
                @endforeach
            </div>


            <div class="bg-white p-4 rounded-xl shadow border">
                <h2 class="text-lg font-bold mb-3">📉 SEO 하위 5개 글</h2>

                @foreach($bottom5 as $p)
                    <div class="border-b py-2">
                        <a href="/posts/{{ $p->id }}" class="font-semibold hover:underline">
                            {{ $p->title }}
                        </a>
                        <p class="text-sm text-gray-500">
                            점수: {{ $p->meta['seo_score'] ?? '-' }} | {{ $p->keyword }}
                        </p>
                    </div>
                @endforeach
            </div>

        </div>


        {{-- 최근 글 5개 --}}
        <div class="bg-white p-4 rounded-xl shadow border">
            <h2 class="text-lg font-bold mb-3">🕒 최근 생성된 글</h2>
            @foreach($posts->take(5) as $p)
                <div class="border-b py-2">
                    <a href="/posts/{{ $p->id }}" class="font-semibold hover:underline">
                        {{ $p->title }}
                    </a>
                    <p class="text-sm text-gray-500">{{ $p->created_at }}</p>
                </div>
            @endforeach
        </div>

    </div>
@endsection
