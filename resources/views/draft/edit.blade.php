@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">임시 저장 글 편집</h1>

    {{-- 프로젝트 선택 --}}
    <label class="font-semibold">프로젝트 선택</label>
    <select id="project_id" class="w-full border p-2 rounded mb-4">
        @foreach($projects as $prj)
            <option value="{{ $prj->id }}" 
                @if($prj->id == $post->project_id) selected @endif>
                {{ $prj->name }}
            </option>
        @endforeach
    </select>

    {{-- 제목 --}}
    <label class="font-semibold">제목</label>
    <input id="title" class="w-full border p-2 rounded mb-3" 
           value="{{ $post->title }}" placeholder="제목 입력">

    {{-- 키워드 --}}
    <label class="font-semibold">키워드</label>
    <input id="keyword" class="w-full border p-2 rounded mb-3"
           value="{{ $post->keyword }}" placeholder="키워드 입력">

    {{-- 본문 HTML --}}
    <label class="font-semibold">내용</label>
    <div id="content_area" class="mt-4 p-4 border rounded bg-white" contenteditable="true">
        {!! $post->html !!}
    </div>

    {{-- 본문 재생성 --}}
    <button onclick="regenerateContent()" 
            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
        내용 다시 생성하기
    </button>

    {{-- 저장 --}}
    <button onclick="publishDraft()" 
            class="mt-4 bg-green-600 text-white px-4 py-2 rounded">
        게시글로 저장하기
    </button>

</div>

<script>
async function regenerateContent() {
    let title = document.getElementById("title").value;
    let keyword = document.getElementById("keyword").value;
    let project_id = document.getElementById("project_id").value;

    let res = await fetch("/api/generate-content", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ project_id, keyword, title })
    });

    let data = await res.json();
    document.getElementById("content_area").innerHTML = data.post.html;
}

async function publishDraft() {
    let project_id = document.getElementById("project_id").value;
    let title = document.getElementById("title").value;
    let keyword = document.getElementById("keyword").value;
    let html = document.getElementById("content_area").innerHTML;

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
            html
        })
    });

    alert("게시글로 저장되었습니다.");
    window.location.href = "/posts";
}
</script>

@endsection
