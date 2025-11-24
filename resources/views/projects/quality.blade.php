@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-8">

        <h1 class="text-3xl font-bold">
            콘텐츠 품질 진단 — {{ $project->name }}
        </h1>

        {{-- 프로젝트 전체 요약 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="font-bold text-xl mb-3">📌 프로젝트 품질 요약</h2>

            <p class="text-gray-700 whitespace-pre-line">
                {{ $analysis['pattern']['summary'] ?? '요약 없음' }}
            </p>

            <h3 class="font-bold mt-4 mb-2">🔥 주요 문제 패턴</h3>
            <ul class="list-disc ml-6 text-sm">
                @foreach($analysis['pattern']['top_problems'] ?? [] as $p)
                    <li>{{ $p }}</li>
                @endforeach
            </ul>

            <h3 class="font-bold mt-4 mb-2">🚀 개선 우선순위</h3>
            <ul class="list-decimal ml-6 text-sm">
                @foreach($analysis['pattern']['priority'] ?? [] as $p)
                    <li>{{ $p }}</li>
                @endforeach
            </ul>
        </div>


        {{-- 글별 품질 분석 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="font-bold text-xl mb-4">📄 글별 품질 분석</h2>

            @foreach($analysis['posts'] ?? [] as $post)
                <div class="border-b pb-4 mb-4">
                    <h3 class="font-bold text-lg">
                        {{ $post['title'] }}
                        <span class="text-sm text-gray-500">({{ $post['keyword'] }})</span>
                    </h3>

                    <p class="mt-1 text-indigo-600 font-bold">
                        품질 점수: {{ $post['score'] }} / 100
                    </p>

                    <h4 class="font-semibold mt-3">문제점</h4>
                    <ul class="list-disc ml-6 text-sm">
                        @foreach($post['problems'] as $p)
                            <li>{{ $p }}</li>
                        @endforeach
                    </ul>

                    <h4 class="font-semibold mt-3">개선 제안</h4>
                    <ul class="list-disc ml-6 text-sm">
                        @foreach($post['suggest'] as $s)
                            <li>{{ $s }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>
@endsection
