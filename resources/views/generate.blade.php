@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">AI Generate</h1>

    {{-- 프로젝트 선택 --}}
    <label class="font-semibold">프로젝트 선택</label>
    <select id="project_id" class="w-full border p-2 rounded mb-4">
        @foreach($projects as $prj)
            <option value="{{ $prj->id }}">{{ $prj->name }}</option>
        @endforeach
    </select>

    {{-- 스타일 선택 --}}
    <label class="font-semibold">스타일 선택</label>
    <select id="style" class="w-full border p-2 rounded mb-4">
        <option value="default">기본</option>
        <option value="emotional">감성적</option>
        <option value="professional">전문적인</option>
        <option value="casual">캐주얼 블로그 스타일</option>
        <option value="short">짧고 간결한</option>
        <option value="seo">SEO 최적화</option>
    </select>

    {{-- 키워드 입력 --}}
    <input id="keyword" class="w-full border p-2 rounded mb-3" placeholder="키워드 입력">

    {{-- 제목 생성 버튼 --}}
    <button onclick="generateTitles()" class="bg-blue-600 text-white px-4 py-2 rounded">
        제목 생성
    </button>

    {{-- 생성된 제목 --}}
    <div id="titles" class="mt-4"></div>

    {{-- 선택된 제목 --}}
    <input id="selected_title" class="w-full border p-2 rounded mt-3" placeholder="선택된 제목">

    {{-- 본문 생성 --}}
    <button onclick="generateContent()" class="mt-4 bg-green-600 text-white px-4 py-2 rounded">
        본문 생성
    </button>

    {{-- 본문 출력 --}}
    <div id="content_area" class="mt-4 p-4 border rounded bg-white"></div>

    <button onclick="analyzeSEO()" 
        class="mt-4 bg-yellow-600 text-white px-4 py-2 rounded">
        SEO 분석하기
    </button>

    <div id="seo_result" class="mt-4 p-4 border rounded bg-white"></div>

    <button onclick="saveDraft()" 
            class="mt-4 bg-gray-600 text-white px-4 py-2 rounded">
        임시 저장
    </button>

    {{-- 저장 버튼 --}}
    <button onclick="savePost()" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded">
        저장하기
    </button>
</div>

<script>
/* -------------------------------
    제목 생성
--------------------------------*/
async function generateTitles() {
    let keyword = document.getElementById("keyword").value;
    let project_id = document.getElementById("project_id").value;
    let style = document.getElementById("style").value;

    let res = await fetch("/api/generate-titles", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ project_id, keyword, style })
    });

    let data = await res.json();

    let html = "";
    data.titles.forEach(t => {
        html += `<div class="p-2 border mb-2 cursor-pointer"
                      onclick="selectTitle('${t}')">
                    ${t}
                 </div>`;
    });
    document.getElementById("titles").innerHTML = html;
}

function selectTitle(title) {
    document.getElementById("selected_title").value = title;
}

/* -------------------------------
    본문 생성
--------------------------------*/
async function generateContent() {
    let title = document.getElementById("selected_title").value;
    let keyword = document.getElementById("keyword").value;
    let project_id = document.getElementById("project_id").value;
    let style = document.getElementById("style").value;

    let res = await fetch("/api/generate-content", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ project_id, keyword, title, style })
    });

    let data = await res.json();
    document.getElementById("content_area").innerHTML = data.post.html;
}

/* -------------------------------
    저장하기
--------------------------------*/
async function savePost() {
    let project_id = document.getElementById("project_id").value;
    let keyword = document.getElementById("keyword").value;
    let title = document.getElementById("selected_title").value;
    let content_html = document.getElementById("content_area").innerHTML;

    let res = await fetch("/api/save-post", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ 
            project_id,
            keyword,
            title,
            html: content_html 
        })
    });

    let data = await res.json();
    alert("저장 완료!");
    window.location.href = "/posts";
}

async function analyzeSEO() {
    let html = document.getElementById("content_area").innerHTML;

    let res = await fetch("/api/analyze-seo", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ html })
    });

    let data = await res.json();

    document.getElementById("seo_result").innerHTML = `
        <h2 class="text-xl font-bold mb-3">SEO 분석 결과</h2>
        <p><strong>SEO 점수:</strong> ${data.score}/100</p>
        <p><strong>추천 메타 설명:</strong> ${data.meta_description}</p>
        <p><strong>핵심 키워드 포함률:</strong> ${data.keyword_density}%</p>
        <p><strong>문서 길이:</strong> ${data.length}자</p>
        <p><strong>개선 포인트:</strong> ${data.recommendation}</p>
    `;
}

async function saveDraft() {
    let project_id = document.getElementById("project_id").value;
    let title = document.getElementById("selected_title").value;
    let keyword = document.getElementById("keyword").value;
    let html = document.getElementById("content_area").innerHTML;

    let res = await fetch("/api/save-draft", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            project_id,
            title,
            keyword,
            html
        })
    });

    let data = await res.json();
    alert("임시 저장 완료!");
}

</script>
@endsection
