@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- Flash 메시지 --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>
    <button 
        id="seoAnalyzeBtn" 
        class="px-4 py-2 bg-purple-600 text-white rounded">
        SEO 분석하기
    </button>

    <div id="seoResult" class="mt-6 hidden bg-white p-4 rounded shadow"></div>

    <p class="text-gray-500 mb-4">{{ $post->created_at }}</p>

    <div class="mb-6 flex gap-3">
        {{-- 워드프레스 업로드 --}}
        <form method="POST" action="/posts/{{ $post->id }}/publish/wordpress">
            @csrf
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                워드프레스 업로드
            </button>
        </form>

        {{-- 티스토리 업로드 --}}
        <form method="POST" action="/posts/{{ $post->id }}/publish/tistory">
            @csrf
            <button class="px-4 py-2 bg-orange-500 text-white rounded">
                티스토리 업로드
            </button>
        </form>
    </div>

    <div class="prose max-w-none bg-white p-4 rounded border">
        {!! $post->html !!}
    </div>

</div>
<script>
document.getElementById('seoAnalyzeBtn').addEventListener('click', () => {

    const postId = {{ $post->id }};
    const title = @json($post->title);
    const html  = @json($post->html);
    const keyword = @json($post->keyword ?? '');

    fetch('/api/analyze-seo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title, html, keyword })
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById('seoResult');
        box.classList.remove('hidden');

        box.innerHTML = `
            <h2 class="text-xl font-bold">SEO 분석 결과</h2>
            <p class="mt-2"><strong>점수:</strong> ${data.score}</p>
            <p><strong>가독성:</strong> ${data.readability}</p>
            <p><strong>키워드 사용:</strong> ${data.keyword_usage}</p>

            <h3 class="mt-4 font-bold">구조 분석</h3>
            <ul class="ml-4 list-disc">
                <li>H1: ${data.structure.h1}</li>
                <li>H2: ${data.structure.h2}</li>
                <li>본문 단락 수: ${data.structure.paragraphs}</li>
            </ul>

            <h3 class="mt-4 font-bold text-red-600">문제점</h3>
            <ul class="ml-4 list-disc">
                ${data.problems.map(v => `<li>${v}</li>`).join('')}
            </ul>

            <h3 class="mt-4 font-bold text-green-600">개선 제안</h3>
            <ul class="ml-4 list-disc">
                ${data.suggestions.map(v => `<li>${v}</li>`).join('')}
            </ul>
        `;
    });
});
</script>

@endsection
