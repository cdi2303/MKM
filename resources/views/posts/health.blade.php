@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-8">

        <h1 class="text-3xl font-bold mb-2">
            글 품질 점검 — {{ $post->title }}
        </h1>

        <p class="text-gray-500 mb-4">
            자동 분석된 글 품질 리포트입니다.
        </p>

        {{-- 1. 점수 카드 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-xl font-bold">가독성 점수</h2>
            <p class="text-5xl font-bold text-indigo-700 mt-2">
                {{ $data['readability_score'] }}
            </p>
        </div>

        {{-- 2. 구조 분석 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4">글 구조 분석</h2>

            <ul class="list-disc ml-5 text-gray-700 leading-7">
                <li>H1 태그: {{ $data['structure']['h1'] }}</li>
                <li>H2 태그: {{ $data['structure']['h2'] }}</li>
                <li>H3 태그: {{ $data['structure']['h3'] }}</li>
                <li>문단 수: {{ $data['structure']['paragraphs'] }}</li>
                <li>평균 문단 길이: {{ $data['structure']['avg_paragraph_length'] }}</li>
            </ul>
        </div>

        {{-- 3. 문제점 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4 text-red-600">문제점</h2>

            <ul class="list-disc ml-5 text-red-700 leading-7">
                @foreach($data['problems'] as $p)
                    <li>{{ $p }}</li>
                @endforeach
            </ul>
        </div>

        {{-- 4. 개선안 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4 text-green-700">개선안</h2>

            <ul class="list-disc ml-5 text-green-700 leading-7">
                @foreach($data['improvements'] as $p)
                    <li>{{ $p }}</li>
                @endforeach
            </ul>

            <div class="mt-6">
                <button id="applyFix"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    ✨ 개선안 자동 적용
                </button>
            </div>
        </div>

        {{-- 5. 개선된 HTML 미리보기 --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4">개선된 본문 (미리보기)</h2>

            <div id="fixedHtmlArea"
                 class="p-4 border rounded bg-gray-50 leading-7 prose max-w-none"
                 contenteditable="true">
                {!! $data['fixed_html'] !!}
            </div>
        </div>

    </div>

    <script>
        document.getElementById('applyFix').onclick = function () {
            fetch("{{ route('posts.health.fix', $post->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    fixed_html: document.getElementById('fixedHtmlArea').innerHTML
                })
            })
                .then(r => r.json())
                .then(() => {
                    alert('개선안이 반영되었습니다.');
                    location.href = "/posts/{{ $post->id }}";
                });
        };
    </script>

@endsection
