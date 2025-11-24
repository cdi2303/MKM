@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-2xl font-bold mb-6">임시 저장 글 편집</h6>

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
            <input id="title" class="w-full border p-2 rounded mb-4"
                   value="{{ $post->title }}">

            {{-- 키워드 --}}
            <label class="font-semibold">키워드</label>
            <input id="keyword" class="w-full border p-2 rounded mb-4"
                   value="{{ $post->keyword }}">

            {{-- HTML 본문 --}}
            <label class="font-semibold">내용</label>
            <div id="content_area"
                 class="mt-4 p-4 border rounded bg-white min-h-[300px]"
                 contenteditable="true">
                {!! $post->html !!}
            </div>

            <div class="flex gap-4 mt-6">

                {{-- 재생성 --}}
                <button id="btn-regenerate"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    내용 다시 생성
                </button>

                {{-- Draft 저장 --}}
                <button id="btn-save-draft"
                        class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Draft 저장
                </button>

                {{-- Post 저장 --}}
                <button id="btn-save-post"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    게시글로 저장
                </button>

            </div>

    </div>

    <script>
        /* ================================
            1) 내용 재생성
        ================================ */
        document.getElementById("btn-regenerate").onclick = function () {
            axios.post('{{ route("generate.content") }}', {
                project_id: document.getElementById("project_id").value,
                keyword:    document.getElementById("keyword").value,
                title:      document.getElementById("title").value,
                style:      "seo"
            }).then(res => {
                document.getElementById("content_area").innerHTML = res.data.html;
            });
        };


        /* ================================
            2) Draft 저장 (is_draft=true)
        ================================ */
        document.getElementById("btn-save-draft").onclick = function () {
            axios.post('{{ route("generate.saveDraft") }}', {
                project_id: document.getElementById("project_id").value,
                keyword:    document.getElementById("keyword").value,
                title:      document.getElementById("title").value,
                html:       document.getElementById("content_area").innerHTML
            }).then(() => {
                alert("Draft 저장 완료!");
            });
        };


        /* ================================
            3) Post 저장 (is_draft=false)
        ================================ */
        document.getElementById("btn-save-post").onclick = function () {
            axios.post('{{ route("generate.savePost") }}', {
                project_id: document.getElementById("project_id").value,
                keyword:    document.getElementById("keyword").value,
                title:      document.getElementById("title").value,
                html:       document.getElementById("content_area").innerHTML
            }).then(() => {
                alert("게시글로 저장되었습니다!");
                window.location.href = "/posts";
            });
        };
    </script>

@endsection
