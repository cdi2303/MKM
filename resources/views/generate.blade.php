@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-6">

        <h1 class="text-3xl font-bold mb-6">
            AI 글 생성 (MKM Generate)
        </h1>

        {{-- 1. 프로젝트 & 기본 정보 --}}
        <div class="bg-white p-4 rounded-xl shadow border space-y-4">
            <div>
                <label class="font-semibold">프로젝트 선택</label>
                <select id="project_id" class="w-full border rounded p-2 mt-1">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-semibold">키워드</label>
                <input id="keyword" class="w-full border rounded p-2 mt-1"
                       placeholder="예: 인공지능 블로그 자동화">
            </div>

            <div>
                <label class="font-semibold">스타일</label>
                <select id="style" class="w-full border rounded p-2 mt-1">
                    <option value="default">기본</option>
                    <option value="emotional">감성적</option>
                    <option value="professional">전문적</option>
                    <option value="casual">캐주얼</option>
                    <option value="short">짧고 간결</option>
                    <option value="seo">SEO 최적화</option>
                </select>
            </div>
        </div>

        {{-- 2. 제목 생성 영역 --}}
        <div class="bg-white p-4 rounded-xl shadow border space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">제목 생성</h2>
                <button id="btn-generate-titles"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    제목 5개 생성
                </button>
            </div>

            <div id="title_suggestions" class="space-y-2">
                {{-- 생성된 제목 버튼들 들어감 --}}
            </div>

            <div class="mt-4">
                <label class="font-semibold">선택된 제목</label>
                <input id="title" class="w-full border rounded p-2 mt-1"
                       placeholder="제목을 선택하거나 직접 입력">
            </div>
        </div>

        {{-- 3. 본문 생성 + 미리보기 --}}
        <div class="bg-white p-4 rounded-xl shadow border space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">본문 생성</h2>
                <button id="btn-generate-content"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    본문 생성하기
                </button>
            </div>

            <div id="meta_description" class="text-sm text-gray-500 mt-1 hidden"></div>

            <div id="content_area"
                 class="mt-4 p-4 border rounded bg-gray-50 min-h-[300px]"
                 contenteditable="true">
                {{-- 생성된 HTML 본문이 들어감 --}}
            </div>
        </div>

        {{-- 4. SEO 분석 / 태그 / 썸네일 / 저장 --}}
        <div class="bg-white p-4 rounded-xl shadow border space-y-4">

            <h2 class="text-xl font-bold mb-2">SEO & 저장</h2>

            <div class="flex flex-wrap gap-3">

                <button id="btn-analyze-seo"
                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    SEO 분석
                </button>

                <button id="btn-generate-tags"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    태그 자동 생성
                </button>

                <button id="btn-generate-thumb"
                        class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    썸네일 생성
                </button>

                <button id="btn-save-draft"
                        class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">
                    Draft로 저장
                </button>

                <button id="btn-save-post"
                        class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">
                    게시글로 저장
                </button>
            </div>

            {{-- SEO 결과 --}}
            <div id="seo_result" class="mt-4 hidden bg-gray-50 p-3 rounded border text-sm whitespace-pre-line"></div>

            {{-- 태그 결과 --}}
            <div id="tag_result" class="mt-4 hidden">
                <h3 class="font-semibold mb-1">추천 태그</h3>
                <div id="tag_list" class="flex flex-wrap gap-2 text-sm"></div>
            </div>

            {{-- 썸네일 미리보기 --}}
            <div id="thumb_box" class="mt-4 hidden">
                <h3 class="font-semibold mb-1">썸네일 미리보기</h3>
                <img id="thumb_img" class="w-64 h-36 object-cover rounded shadow">
            </div>

        </div>

    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        function getPayload() {
            return {
                project_id: document.getElementById('project_id').value,
                keyword:    document.getElementById('keyword').value,
                style:      document.getElementById('style').value,
                title:      document.getElementById('title').value,
                html:       document.getElementById('content_area').innerHTML
            };
        }

        /* -----------------------------
           1) 제목 생성
        ----------------------------- */
        document.getElementById('btn-generate-titles').onclick = function () {
            const keyword = document.getElementById('keyword').value;
            const style   = document.getElementById('style').value;

            fetch("{{ route('generate.titles') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ keyword, style })
            })
                .then(r => r.json())
                .then(data => {
                    const box = document.getElementById('title_suggestions');
                    box.innerHTML = '';

                    (data.titles || []).forEach(t => {
                        const btn = document.createElement('button');
                        btn.className = "px-3 py-1 border rounded mr-2 mb-2 hover:bg-gray-100 text-sm";
                        btn.textContent = t;
                        btn.onclick = () => {
                            document.getElementById('title').value = t;
                        };
                        box.appendChild(btn);
                    });
                });
        };

        /* -----------------------------
           2) 본문 생성
        ----------------------------- */
        document.getElementById('btn-generate-content').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.content') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    keyword: payload.keyword,
                    title: payload.title,
                    style: payload.style
                })
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('content_area').innerHTML = data.html || '';
                    const md = document.getElementById('meta_description');
                    if (data.meta && data.meta.description) {
                        md.classList.remove('hidden');
                        md.textContent = 'Meta Description: ' + data.meta.description;
                    }
                });
        };

        /* -----------------------------
           3) SEO 분석
        ----------------------------- */
        document.getElementById('btn-analyze-seo').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.analyze') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    keyword: payload.keyword,
                    title: payload.title,
                    html: payload.html
                })
            })
                .then(r => r.json())
                .then(data => {
                    const box = document.getElementById('seo_result');
                    box.classList.remove('hidden');
                    box.textContent = JSON.stringify(data, null, 2);
                });
        };

        /* -----------------------------
           4) 태그 자동 생성
        ----------------------------- */
        document.getElementById('btn-generate-tags').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.tags') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    keyword: payload.keyword,
                    title: payload.title,
                    html: payload.html
                })
            })
                .then(r => r.json())
                .then(data => {
                    const tags = data.tags || [];
                    const box  = document.getElementById('tag_list');
                    const wrap = document.getElementById('tag_result');

                    box.innerHTML = '';
                    tags.forEach(t => {
                        const span = document.createElement('span');
                        span.className = "px-3 py-1 bg-blue-100 text-blue-700 rounded-full";
                        span.textContent = t;
                        box.appendChild(span);
                    });

                    if (tags.length > 0) {
                        wrap.classList.remove('hidden');
                    }
                });
        };

        /* -----------------------------
           5) 썸네일 생성
        ----------------------------- */
        document.getElementById('btn-generate-thumb').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.thumbnail') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    title: payload.title,
                    html: payload.html
                })
            })
                .then(r => r.json())
                .then(data => {
                    const box = document.getElementById('thumb_box');
                    const img = document.getElementById('thumb_img');

                    img.src = data.thumbnail || '';
                    box.classList.remove('hidden');
                });
        };

        /* -----------------------------
           6) Draft 저장
        ----------------------------- */
        document.getElementById('btn-save-draft').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.saveDraft') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(() => {
                    alert('Draft로 저장되었습니다.');
                    window.location.href = '/drafts';
                });
        };

        /* -----------------------------
           7) 게시글로 저장
        ----------------------------- */
        document.getElementById('btn-save-post').onclick = function () {
            const payload = getPayload();

            fetch("{{ route('generate.savePost') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(() => {
                    alert('게시글로 저장되었습니다.');
                    window.location.href = '/posts';
                });
        };
    </script>
@endsection
