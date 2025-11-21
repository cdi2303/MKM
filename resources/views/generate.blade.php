@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">AI 글 생성</h1>

    {{-- 키워드 입력 --}}
    <div class="mb-4">
        <label class="block font-semibold mb-1">키워드</label>
        <input type="text" id="keyword" class="w-full border p-2 rounded" placeholder="예: 건강한 다이어트 저당 식단">
    </div>

    {{-- 스타일 선택 --}}
    <div class="mb-4">
        <label class="block font-semibold mb-1">스타일 프리셋</label>
        <select id="style" class="w-full border p-2 rounded">
            <option value="default">기본</option>
            <option value="blog">블로그 스타일</option>
            <option value="seo">SEO 최적화</option>
            <option value="short">짧고 간단하게</option>
        </select>
    </div>

    {{-- 프로젝트 선택 --}}
    <div class="mb-4">
        <label class="block font-semibold mb-1">프로젝트 선택</label>
        <select id="project_id" class="w-full border p-2 rounded">
            <option value="">선택하세요</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- 키워드 탐색 버튼 --}}
    <button 
        id="exploreBtn"
        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded mb-6">
        🔍 키워드 자동 탐색
    </button>

    <div id="keywordResult" class="hidden p-4 bg-white border rounded mb-6"></div>

    {{-- 제목 생성 버튼 --}}
    <button 
        onclick="generateTitles()" 
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4">
        ✨ 제목 생성
    </button>

    {{-- 제목 리스트 --}}
    <div id="titleSection" class="mt-6 hidden">
        <h2 class="text-xl font-bold mb-2">추천 제목</h2>
        <ul id="titleList" class="list-disc ml-6"></ul>
    </div>

    {{-- 본문 생성 영역 --}}
    <div id="contentSection" class="mt-8 hidden">
        <h2 class="text-xl font-bold mb-3">📝 생성된 본문</h2>
        <div id="contentArea" class="border p-4 bg-white rounded max-h-[500px] overflow-y-auto"></div>

        {{-- SEO 분석 버튼 --}}
        <button 
            id="seoAnalyzeBtn"
            class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded mt-4">
            🔎 SEO 분석하기
        </button>

        {{-- 자동 개선 버튼 --}}
        <button 
            id="upgradeContentBtn"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded mt-4 ml-2">
            🚀 SEO 자동 개선
        </button>

        {{-- 자동 태그 생성 --}}
        <button 
            id="tagGenerateBtn"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded mt-4 ml-2">
            🏷️ 자동 태그 생성
        </button>

        {{-- 내부 링크 추천 --}}
        <button 
            id="internalLinkBtn"
            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded mt-4 ml-2">
            🔗 내부 링크 추천
        </button>

        {{-- SEO 결과 --}}
        <div id="seoResult" class="mt-6 hidden bg-white border p-4 rounded"></div>

        {{-- 태그 출력 --}}
        <div id="tagBox" class="hidden bg-white border rounded p-4 mt-4"></div>

        {{-- 내부 링크 추천 결과 --}}
        <div id="internalLinkBox" class="hidden bg-white border rounded p-4 mt-4"></div>

        {{-- 저장 버튼 --}}
        <button 
            onclick="savePost()" 
            class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded mt-6">
            💾 저장하기
        </button>
    </div>

    <button 
        id="thumbnailBtn"
        class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded mt-4">
        🖼️ 썸네일 자동 생성
    </button>

    <div id="thumbnailPreview" class="hidden mt-4">
        <h2 class="font-bold text-lg mb-2">썸네일 미리보기</h2>
        <img id="thumbnailImage" class="w-80 rounded shadow">
    </div>
</div>

<script>
// 1) 제목 생성
function generateTitles() {
    const keyword = keywordInput().value;
    const style = styleInput().value;
    const project_id = projectInput().value;

    fetch('/api/generate-titles', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ keyword, style, project_id })
    })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('titleList');
        list.innerHTML = '';

        (data.titles || []).forEach(t => {
            const li = document.createElement('li');
            li.textContent = t;
            li.classList.add('cursor-pointer', 'text-blue-600', 'hover:underline');
            li.onclick = () => generateContent(t);
            list.appendChild(li);
        });

        document.getElementById('titleSection').classList.remove('hidden');
    });
}

// 2) 본문 생성
function generateContent(title) {
    const keyword = keywordInput().value;

    fetch('/api/generate-content', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ keyword, title })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('contentArea').innerHTML = data.html;
        document.getElementById('contentSection').classList.remove('hidden');
    });
}

// 3) 저장 기능
function savePost() {
    const project_id = projectInput().value;
    const html = document.getElementById('contentArea').innerHTML;
    const keyword = keywordInput().value;

    fetch('/api/save-post', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            project_id,
            keyword,
            html 
        })
    })
    .then(() => alert('저장 완료!'));
}

// 4) SEO 분석
document.getElementById('seoAnalyzeBtn').addEventListener('click', () => {
    const html = document.getElementById('contentArea').innerHTML;
    const keyword = keywordInput().value;

    fetch('/api/analyze-seo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ html, keyword })
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

            <h3 class="font-bold mt-4">문제점</h3>
            <ul class="list-disc ml-6">${data.problems.map(v => `<li>${v}</li>`).join('')}</ul>

            <h3 class="font-bold mt-4">개선 제안</h3>
            <ul class="list-disc ml-6">${data.suggestions.map(v => `<li>${v}</li>`).join('')}</ul>
        `;
    });
});

// 5) SEO 자동 개선
document.getElementById('upgradeContentBtn').addEventListener('click', () => {
    const html = document.getElementById('contentArea').innerHTML;
    const keyword = keywordInput().value;

    fetch('/api/upgrade-content', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ html, keyword })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('contentArea').innerHTML = data.html;
        alert('SEO 자동 개선 완료!');
    });
});

// 6) 태그 자동 생성
document.getElementById('tagGenerateBtn').addEventListener('click', () => {
    const keyword = keywordInput().value;
    const html    = document.getElementById('contentArea').innerHTML;

    fetch('/api/generate-tags', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ keyword, html })
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById('tagBox');
        box.classList.remove('hidden');

        box.innerHTML = `
            <h2 class="font-bold text-lg mb-2">추천 태그</h2>
            <div class="flex flex-wrap gap-2">
                ${data.tags.map(t => `<span class="px-2 py-1 bg-gray-200 rounded">${t}</span>`).join('')}
            </div>
        `;
    });
});

// 7) 내부 링크 추천
document.getElementById('internalLinkBtn').addEventListener('click', () => {
    const project_id = projectInput().value;
    const keyword = keywordInput().value;
    const html = document.getElementById('contentArea').innerHTML;

    fetch('/api/recommend-internal-links', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ project_id, keyword, html })
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById('internalLinkBox');
        box.classList.remove('hidden');

        box.innerHTML = `
            <h2 class="font-bold text-lg mb-2">추천 내부 링크</h2>
            <ul class="list-disc ml-6">
                ${
                    data.links
                    .map(v=>`<li><a href="/posts/${v.id}" target="_blank" class="text-blue-600 underline">${v.title}</a></li>`)
                    .join('')
                }
            </ul>
        `;
    });
});

// 8) 키워드 탐색
document.getElementById('exploreBtn').addEventListener('click', () => {
    const keyword = keywordInput().value;

    fetch('/api/explore-keyword', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ keyword })
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById('keywordResult');
        box.classList.remove('hidden');

        box.innerHTML = `
            <h2 class="font-bold text-xl">🔍 키워드 분석 결과</h2>

            <h3 class="mt-3 font-semibold">연관 키워드</h3>
            <ul class="list-disc ml-6">
                ${data.related.map(v => `<li>${v.keyword} (${v.intent}, 난이도 ${v.difficulty})</li>`).join('')}
            </ul>

            <h3 class="mt-4 font-semibold">롱테일 키워드</h3>
            <ul class="list-disc ml-6">
                ${data.longtail.map(v => `<li>${v}</li>`).join('')}
            </ul>
        `;
    });
});

document.getElementById('thumbnailBtn').addEventListener('click', () => {
    const title = document.querySelector('#titleList li')?.textContent || keywordInput().value;
    const html = document.getElementById('contentArea').innerHTML;

    fetch('/api/generate-thumbnail', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, html })
    })
    .then(r => r.json())
    .then(data => {
        if (data.thumbnail) {
            document.getElementById('thumbnailPreview').classList.remove('hidden');
            document.getElementById('thumbnailImage').src = data.thumbnail;
        }
    });
});


// Helper
function keywordInput(){ return document.getElementById('keyword'); }
function styleInput(){ return document.getElementById('style'); }
function projectInput(){ return document.getElementById('project_id'); }

</script>
@endsection
