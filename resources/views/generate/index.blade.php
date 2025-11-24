@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        {{-- 페이지 제목 --}}
        <h1 class="text-3xl font-bold mb-8">AI 콘텐츠 생성</h1>

        {{-- STEP 1: 키워드 & 스타일 --}}
        <div class="bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">1. 키워드 입력</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="font-semibold">프로젝트</label>
                    <select id="project_id" class="w-full border p-2 rounded">
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold">키워드</label>
                    <input type="text" id="keyword" class="w-full border p-2 rounded">
                </div>

                <div>
                    <label class="font-semibold">스타일</label>
                    <select id="style" class="w-full border p-2 rounded">
                        <option value="default">기본</option>
                        <option value="emotional">감성적</option>
                        <option value="professional">전문적</option>
                        <option value="casual">캐주얼</option>
                        <option value="short">간결형</option>
                        <option value="seo">SEO 최적화형</option>
                    </select>
                </div>
            </div>

            <button id="btn-generate-titles"
                    class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                제목 생성
            </button>
        </div>


        {{-- STEP 2: 제목 결과 --}}
        <div id="title-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">2. 생성된 제목 선택</h2>

            <div id="titles-container" class="space-y-2"></div>
        </div>


        {{-- STEP 3: 본문 생성 --}}
        <div id="content-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">3. 본문 생성</h2>

            <div>
                <label class="font-semibold">선택된 제목</label>
                <input type="text" id="selected-title" class="w-full border p-2 rounded mb-4" readonly>

                <button id="btn-generate-content"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    본문 생성
                </button>
            </div>
        </div>


        {{-- STEP 4: 결과 미리보기 --}}
        <div id="result-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">4. 생성 결과</h2>

            <div id="generated-html" class="prose max-w-none border p-4 rounded"></div>

            <div class="mt-6 flex gap-4">

                <button id="btn-analyze"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    SEO 분석하기
                </button>

                <button id="btn-tags"
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    태그 생성
                </button>

                <button id="btn-thumbnail"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    썸네일 생성
                </button>

                <button id="btn-save-draft"
                        class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Draft 저장
                </button>

                <button id="btn-save-post"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Post 저장
                </button>

            </div>
        </div>


        {{-- STEP 5: SEO 분석 출력 --}}
        <div id="seo-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">SEO 분석 결과</h2>
            <pre id="seo-result" class="bg-gray-50 p-4 rounded text-sm"></pre>
        </div>


        {{-- STEP 6: 태그 출력 --}}
        <div id="tag-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">추천 태그</h2>
            <div id="tag-result" class="flex gap-2 flex-wrap"></div>
        </div>


        {{-- STEP 7: 썸네일 출력 --}}
        <div id="thumb-section" class="hidden bg-white p-6 border rounded-xl shadow mb-6">
            <h2 class="text-xl font-bold mb-4">썸네일 미리보기</h2>
            <img id="thumb-preview" class="rounded-xl shadow max-w-lg">
        </div>

    </div>

@endsection


@section('scripts')
    <script>
        /* ================================
            1) 제목 생성
        ================================ */
        document.getElementById('btn-generate-titles').onclick = function() {
            let keyword = document.getElementById('keyword').value;
            let style = document.getElementById('style').value;

            axios.post('{{ route("generate.titles") }}', {
                keyword: keyword,
                style: style
            }).then(res => {
                let titles = res.data.titles;

                let html = '';
                titles.forEach(t => {
                    html += `<button class="block w-full text-left p-2 border rounded hover:bg-gray-100"
                             onclick="selectTitle('${t.replace(/'/g, "\\'")}')">${t}</button>`;
                });

                document.getElementById('titles-container').innerHTML = html;
                document.getElementById('title-section').classList.remove('hidden');
            });
        };

        window.selectTitle = function(t) {
            document.getElementById('selected-title').value = t;
            document.getElementById('content-section').classList.remove('hidden');
        };


        /* ================================
            2) 본문 생성
        ================================ */
        document.getElementById('btn-generate-content').onclick = function() {
            let keyword = document.getElementById('keyword').value;
            let title = document.getElementById('selected-title').value;
            let style = document.getElementById('style').value;

            axios.post('{{ route("generate.content") }}', {
                keyword: keyword,
                title: title,
                style: style
            }).then(res => {
                let html = res.data.html;

                document.getElementById('generated-html').innerHTML = html;
                document.getElementById('result-section').classList.remove('hidden');
            });
        };


        /* ================================
            3) SEO 분석
        ================================ */
        document.getElementById('btn-analyze').onclick = function() {
            let keyword = document.getElementById('keyword').value;
            let title = document.getElementById('selected-title').value;
            let html = document.getElementById('generated-html').innerHTML;

            axios.post('{{ route("generate.analyze") }}', {
                keyword: keyword,
                title: title,
                html: html
            }).then(res => {
                document.getElementById('seo-result').innerText = JSON.stringify(res.data, null, 2);
                document.getElementById('seo-section').classList.remove('hidden');
            });
        };


        /* ================================
            4) 태그 생성
        ================================ */
        document.getElementById('btn-tags').onclick = function() {
            let keyword = document.getElementById('keyword').value;
            let title = document.getElementById('selected-title').value;
            let html = document.getElementById('generated-html').innerHTML;

            axios.post('{{ route("generate.tags") }}', {
                keyword, title, html
            }).then(res => {
                let tags = res.data.tags;

                let html = '';
                tags.forEach(t => {
                    html += `<span class="px-3 py-1 bg-gray-200 rounded-lg">${t}</span>`;
                });

                document.getElementById('tag-result').innerHTML = html;
                document.getElementById('tag-section').classList.remove('hidden');
            });
        };


        /* ==========================*
