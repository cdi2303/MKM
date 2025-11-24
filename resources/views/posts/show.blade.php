@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        {{-- Flash 메시지 --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 border text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 border text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif


        {{-- 제목 --}}
        <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>

        {{-- 프로젝트/키워드 --}}
        <p class="text-gray-500 text-sm mb-3">
            프로젝트: <strong>{{ $post->project->name ?? '-' }}</strong> |
            키워드: <strong>{{ $post->keyword }}</strong>
        </p>


        {{-- 썸네일 --}}
        @if($post->thumbnail_url)
            <img src="{{ $post->thumbnail_url }}"
                 class="w-full max-w-xl rounded mb-6 shadow">
        @endif


        {{-- AI 기능 버튼들 --}}
        <div class="flex flex-wrap gap-3 mb-6">

            <button id="seoAnalyzeBtn"
                    class="px-4 py-2 bg-purple-600 text-white rounded">
                SEO 분석하기
            </button>

            <button id="upgradeContentBtn"
                    class="px-4 py-2 bg-green-600 text-white rounded">
                SEO 자동 개선하기
            </button>

            <button id="generateTagsBtn"
                    class="px-4 py-2 bg-indigo-600 text-white rounded">
                🔖 자동 태그 생성하기
            </button>

            <button id="internalLinkBtn"
                    class="px-4 py-2 bg-yellow-600 text-white rounded">
                🔗 내부 링크 추천하기
            </button>

            <button onclick="generateABTitles()"
                    class="px-4 py-2 bg-blue-700 text-white rounded">
                제목 AB 테스트 생성
            </button>


            <a href="/posts/{{ $post->id }}/versions"
               class="px-4 py-2 bg-gray-700 text-white rounded">
                버전 히스토리 보기
            </a>
        </div>


        {{-- SEO 분석 결과 출력 박스 --}}
        <div id="seoResult" class="mt-6 hidden bg-white p-4 rounded shadow"></div>


        {{-- 게시글 본문 --}}
        <div class="prose max-w-none bg-white p-4 border rounded shadow">
            {!! $post->html !!}
        </div>

        <div id="internalLinkBox" class="mt-10 hidden bg-white p-4 border rounded shadow">
            <h3 class="text-xl font-bold mb-3">내부 링크 추천 결과</h3>
            <ul id="internalLinkList" class="list-disc ml-5"></ul>
        </div>

        {{-- Meta 정보 --}}
        @if(isset($post->meta['description']) || isset($post->meta['seo_score']))
            <div class="mt-8 p-4 bg-gray-50 rounded border">
                <h3 class="text-xl font-bold mb-2">메타 정보</h3>

                @if(isset($post->meta['seo_score']))
                    <p class="mb-2">SEO 점수:
                        <strong>{{ $post->meta['seo_score'] }}</strong>
                    </p>
                @endif

                @if(isset($post->meta['description']))
                    <p class="text-gray-600">
                        <strong>Description:</strong> {{ $post->meta['description'] }}
                    </p>
                @endif
            </div>
        @endif


        {{-- 태그 UI --}}
        <hr class="my-8">

        <div class="mt-6">
            <h3 class="text-xl font-bold mb-2">태그</h3>

            @if(isset($post->meta['tags']) && count($post->meta['tags']) > 0)
                <div id="tagList" class="flex flex-wrap gap-2 mb-3">
                    @foreach($post->meta['tags'] as $tag)
                        <span class="px-3 py-1 bg-gray-200 rounded-full text-sm">
                        {{ $tag }}
                    </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 mb-3">등록된 태그가 없습니다.</p>
                <div id="tagList" class="flex flex-wrap gap-2 mb-3"></div>
            @endif
        </div>


        {{-- 업로드 버튼 --}}
        <div class="mb-6 flex gap-3 mt-10">

            <form method="POST" action="/posts/{{ $post->id }}/publish/wordpress">
                @csrf
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    워드프레스 업로드
                </button>
            </form>

            <form method="POST" action="/posts/{{ $post->id }}/publish/tistory">
                @csrf
                <button class="px-4 py-2 bg-orange-500 text-white rounded">
                    티스토리 업로드
                </button>
            </form>

        </div>

    </div>




    {{-- ------------------------------------------------------------
        JS 기능들
    ------------------------------------------------------------- --}}

    <script>
        /* -----------------------------------------
           1) SEO 분석
        ----------------------------------------- */
        document.getElementById('seoAnalyzeBtn').addEventListener('click', () => {

            const title = @json($post->title);
            const html  = @json($post->html);
            const keyword = @json($post->keyword ?? '');

            fetch("{{ route('generate.analyze') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
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



        /* -----------------------------------------
           2) SEO 자동 개선
        ----------------------------------------- */
        document.getElementById('upgradeContentBtn').addEventListener('click', () => {

            const title = @json($post->title);
            const html  = @json($post->html);
            const keyword = @json($post->keyword);

            fetch("{{ route('generate.upgrade') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
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
                            fetch("{{ route('generate.savePost') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    id: {{ $post->id }},
                                    html: data.html,
                                    title: @json($post->title),
                                    keyword: @json($post->keyword)
                                })
                            }).then(() => {
                                location.reload();
                            });
                        }
                    });
                });
        });



        /* -----------------------------------------
           3) 자동 태그 생성
        ----------------------------------------- */
        document.getElementById('generateTagsBtn').addEventListener('click', () => {
            const title = @json($post->title);
            const keyword = @json($post->keyword);
            const html = @json($post->html);

            fetch("{{ route('generate.tags') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ title, keyword, html })
            })
                .then(r => r.json())
                .then(data => {

                    let tags = data.tags ?? [];

                    const tagBox = document.getElementById('tagList');
                    tagBox.innerHTML = '';

                    tags.forEach(tag => {
                        tagBox.innerHTML += `
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    ${tag}
                </span>
            `;
                    });

                    // 태그를 DB에 반영
                    fetch("{{ route('generate.savePost') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            id: {{ $post->id }},
                            html: @json($post->html),
                            title: @json($post->title),
                            keyword: @json($post->keyword),
                            tags: tags
                        })
                    });
                });
        });

        /* -----------------------------------------
   4) 내부 링크 추천 기능
----------------------------------------- */
        document.getElementById('internalLinkBtn').addEventListener('click', () => {

            const postId = {{ $post->id }};
            const html = @json($post->html);
            const keyword = @json($post->keyword);

            fetch("{{ route('generate.internalLinks') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    project_id: {{ $post->project_id }},
                    post_id: postId,
                    html: html,
                    keyword: keyword
                })
            })
                .then(res => res.json())
                .then(data => {
                    const box = document.getElementById("internalLinkBox");
                    const list = document.getElementById("internalLinkList");

                    box.classList.remove("hidden");
                    list.innerHTML = "";

                    data.links.forEach(item => {
                        list.innerHTML += `
                <li>
                    <a href="/posts/${item.id}" class="text-blue-600 underline">
                        ${item.title} (${item.keyword})
                    </a>
                </li>
            `;
                    });
                });
        });

        function generateABTitles() {
            fetch(`/posts/{{ $post->id }}/generate-title-tests`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
                .then(r => r.json())
                .then(d => {
                    alert("테스트용 제목 5개 생성 완료!");
                    console.log(d.titles);
                });
        }


    </script>

@endsection
