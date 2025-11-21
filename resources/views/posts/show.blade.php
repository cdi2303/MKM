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

    <button 
        id="upgradeContentBtn" 
        class="px-4 py-2 bg-green-600 text-white rounded mt-3">
        SEO 자동 개선하기
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

document.getElementById('upgradeContentBtn').addEventListener('click', () => {
    
    const title = @json($post->title);
    const html  = @json($post->html);
    const keyword = @json($post->keyword);

    fetch('/api/upgrade-content', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title, html, keyword })
    })
    .then(r => r.json())
    .then(data => {
        Swal.fire({
            title: '개선된 콘텐츠 확인',
            html: `
                <div class="text-left">
                    <h3 class="font-bold mb-2">🔧 변경된 사항</h3>
                    <ul class="list-disc ml-6">
                        ${data.changes.map(v => `<li>${v}</li>`).join('')}
                    </ul>

                    <h3 class="font-bold mt-4 mb-2">📄 개선된 본문</h3>
                    <div class="p-3 border rounded bg-gray-50" style="max-height: 400px; overflow-y: auto;">
                        ${data.html}
                    </div>

                    <h3 class="font-bold mt-4 mb-2">🆚 변경 비교</h3>
                    <div class="p-3 border rounded bg-gray-50">
                        ${data.diff}
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '본문에 반영하기',
            cancelButtonText: '닫기'
        }).then(result => {
            if (result.isConfirmed) {
                // Ajax로 글 업데이트
                fetch('/api/save-post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: {{ $post->id }},
                        html: data.html
                    })
                }).then(() => {
                    location.reload();
                });
            }
        });
    });
});

</script>

@endsection
