@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-6">

        <h1 class="text-3xl font-bold mb-6 flex items-center gap-3">
            AI 글 생성 (MKM Generate)
            <span class="text-sm px-2 py-1 bg-gray-100 text-gray-600 rounded border">
                로컬 LLM · 자동 SEO 지원
            </span>
        </h1>

        {{-- 0. 상단 안내/상태 --}}
        <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl p-4 text-sm flex items-start gap-3">
            <div class="mt-0.5">💡</div>
            <div>
                <p class="font-semibold mb-1">사용 가이드</p>
                <ul class="list-disc ml-5 space-y-1">
                    <li>프로젝트, 키워드, 스타일을 먼저 설정한 뒤 <strong>제목 5개 생성</strong> 버튼으로 후보를 뽑으세요.</li>
                    <li>마음에 드는 제목을 선택 후 <strong>본문 생성하기</strong>로 초안 본문을 만든 뒤, 우측에서 직접 수정할 수 있습니다.</li>
                    <li>SEO 분석 · 태그 생성 · 썸네일 생성까지 한 번에 처리하고, <strong>Draft 또는 게시글로 저장</strong>하세요.</li>
                </ul>
            </div>
        </div>

        {{-- 메인 2컬럼 레이아웃 --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: 설정 + 제목 + 생성 버튼 --}}
            <div class="space-y-6 lg:col-span-1">

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
                        <div class="flex items-center justify-between">
                            <label class="font-semibold">키워드</label>
                            <span id="keyword_length" class="text-xs text-gray-400">0자</span>
                        </div>
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
                                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                            제목 5개 생성
                        </button>
                    </div>

                    <p class="text-xs text-gray-500">
                        키워드와 스타일을 기반으로 클릭률이 높은 제목 5개를 제안합니다.
                        버튼을 클릭하면 아래에 후보가 나타나며, 클릭 시 자동으로 선택됩니다.
                    </p>

                    <div id="title_suggestions" class="space-y-2">
                        {{-- 생성된 제목 버튼들 --}}
                    </div>

                    <div class="mt-4 space-y-1">
                        <label class="font-semibold">선택된 제목</label>
                        <input id="title" class="w-full border rounded p-2 mt-1"
                               placeholder="제목을 선택하거나 직접 입력">
                        <p class="text-xs text-gray-400">
                            제목 길이는 30~60자 사이를 권장합니다.
                        </p>
                    </div>
                </div>

                {{-- 3. 저장 관련 버튼 (작은 화면에서는 상단에) --}}
                <div class="bg-white p-4 rounded-xl shadow border space-y-4 lg:hidden">
                    <h2 class="text-lg font-bold">저장 / 후처리</h2>
                    <div class="flex flex-wrap gap-3">
                        <button id="btn-save-draft"
                                class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 text-sm">
                            Draft로 저장
                        </button>

                        <button id="btn-save-post"
                                class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800 text-sm">
                            게시글로 저장
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">
                        Draft는 초안 보관용이며, 게시글 저장은 바로 게시 리스트에 노출됩니다.
                    </p>
                </div>

            </div>

            {{-- RIGHT: 본문 / SEO / 태그 / 썸네일 / 저장 --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- 3. 본문 생성 + 미리보기 --}}
                <div class="bg-white p-4 rounded-xl shadow border space-y-4">

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold">본문 생성</h2>
                            <p class="text-xs text-gray-500">
                                생성된 본문은 아래 영역에서 직접 수정할 수 있습니다.
                            </p>
                        </div>
                        <button id="btn-generate-content"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                            본문 생성하기
                        </button>
                    </div>

                    <div id="meta_description"
                         class="text-xs text-gray-600 mt-1 hidden bg-gray-50 border rounded p-2">
                        {{-- Meta Description 표시 --}}
                    </div>

                    <div id="content_area"
                         class="mt-4 p-4 border rounded bg-gray-50 min-h-[350px] prose max-w-none overflow-y-auto"
                         contenteditable="true">
                        {{-- 생성된 HTML 본문 --}}
                    </div>

                    <p class="text-xs text-gray-400 text-right">
                        ✏️ 이 영역은 자유롭게 수정 가능합니다. (HTML 구조 유지 권장)
                    </p>
                </div>

                {{-- 4. SEO 분석 / 태그 / 썸네일 / 저장 --}}
                <div class="bg-white p-4 rounded-xl shadow border space-y-4">

                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">SEO & 자동화 도구</h2>
                        <span id="ai_status" class="text-xs text-gray-400">
                            대기 중
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button id="btn-analyze-seo"
                                class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                            SEO 분석
                        </button>

                        <button id="btn-generate-tags"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                            태그 자동 생성
                        </button>

                        <button id="btn-generate-thumb"
                                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">
                            썸네일 생성
                        </button>

                        <div class="hidden lg:flex flex-wrap gap-3 ml-auto">
                            <button id="btn-save-draft-desktop"
                                    class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 text-sm">
                                Draft로 저장
                            </button>

                            <button id="btn-save-post-desktop"
                                    class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800 text-sm">
                                게시글로 저장
                            </button>
                        </div>
                    </div>

                    {{-- SEO 결과 --}}
                    <div id="seo_result"
                         class="mt-4 hidden bg-gray-50 p-3 rounded border text-sm">
                        {{-- 구조화된 SEO 분석 결과 --}}
                    </div>

                    {{-- 태그 결과 --}}
                    <div id="tag_result" class="mt-4 hidden">
                        <h3 class="font-semibold mb-1 text-sm">추천 태그</h3>
                        <div id="tag_list" class="flex flex-wrap gap-2 text-sm"></div>
                        <p class="text-xs text-gray-400 mt-1">
                            태그는 저장 시 함께 반영됩니다. (중복 클릭으로 선택/해제 가능 방식으로 구현 예정)
                        </p>
                    </div>

                    {{-- 썸네일 미리보기 --}}
                    <div id="thumb_box" class="mt-4 hidden">
                        <h3 class="font-semibold mb-1 text-sm">썸네일 미리보기</h3>
                        <img id="thumb_img" class="w-64 h-36 object-cover rounded shadow border">
                    </div>

                </div>
            </div>
        </div>

        {{-- 에러 표시 영역 --}}
        <div id="error_box"
             class="hidden mt-4 p-3 rounded border border-red-200 bg-red-50 text-sm text-red-800">
        </div>

    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        function getPayload() {
            return {
                project_id: document.getElementById('project_id').value,
                keyword:    document.getElementById('keyword').value.trim(),
                style:      document.getElementById('style').value,
                title:      document.getElementById('title').value.trim(),
                html:       document.getElementById('content_area').innerHTML
            };
        }

        function setLoading(button, isLoading, loadingText) {
            if (!button) return;
            if (isLoading) {
                button.dataset.originalText = button.textContent;
                button.textContent = loadingText;
                button.disabled = true;
                button.classList.add('opacity-70', 'cursor-not-allowed');
            } else {
                if (button.dataset.originalText) {
                    button.textContent = button.dataset.originalText;
                }
                button.disabled = false;
                button.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }

        function setAiStatus(text) {
            const el = document.getElementById('ai_status');
            if (!el) return;
            el.textContent = text;
        }

        function showError(msg) {
            const box = document.getElementById('error_box');
            box.textContent = msg;
            box.classList.remove('hidden');
        }

        function clearError() {
            const box = document.getElementById('error_box');
            box.classList.add('hidden');
            box.textContent = '';
        }

        // 키워드 글자수 표시
        document.getElementById('keyword').addEventListener('input', (e) => {
            const len = e.target.value.length;
            document.getElementById('keyword_length').textContent = len + '자';
        });

        /* -----------------------------
           1) 제목 생성
        ----------------------------- */
        document.getElementById('btn-generate-titles')?.addEventListener('click', () => {
            const keyword = document.getElementById('keyword').value;
            const style   = document.getElementById('style').value;

            fetch("/generate/titles", {
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

                data.titles.forEach(t => {
                    const btn = document.createElement('button');
                    btn.className = "px-3 py-1 border rounded hover:bg-gray-100 text-sm";
                    btn.textContent = t;
                    btn.onclick = () => document.getElementById('title').value = t;
                    box.appendChild(btn);
                });
            });
        });

        /* -----------------------------
           2) 본문 생성
        ----------------------------- */
        document.getElementById('btn-generate-content').onclick = function () {
            clearError();
            const payload = getPayload();
            const btn = this;

            if (!payload.keyword || !payload.title) {
                showError('키워드와 제목을 먼저 입력해주세요.');
                return;
            }

            setLoading(btn, true, '본문 생성 중...');
            setAiStatus('본문 생성 중...');

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
                    } else {
                        md.classList.add('hidden');
                        md.textContent = '';
                    }
                })
                .catch(() => {
                    showError('본문 생성 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    setLoading(btn, false);
                    setAiStatus('대기 중');
                });
        };

        /* -----------------------------
           3) SEO 분석
        ----------------------------- */
        document.getElementById('btn-analyze-seo').onclick = function () {
            clearError();
            const payload = getPayload();
            const btn = this;

            if (!payload.title || !payload.html) {
                showError('제목과 본문이 있어야 SEO 분석이 가능합니다.');
                return;
            }

            setLoading(btn, true, '분석 중...');
            setAiStatus('SEO 분석 중...');

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

                    if (typeof data === 'object' && data.score !== undefined) {
                        box.innerHTML = `
<div class="space-y-2">
    <div class="flex items-center justify-between">
        <span class="font-semibold">SEO 점수</span>
        <span class="text-lg font-bold text-purple-700">${data.score}</span>
    </div>
    <p><strong>가독성:</strong> ${data.readability ?? '-'}</p>
    <p><strong>키워드 사용:</strong> ${data.keyword_usage ?? '-'}</p>

    ${data.structure ? `
    <div class="mt-2">
        <h4 class="font-semibold text-sm mb-1">구조 분석</h4>
        <ul class="list-disc ml-5 text-xs space-y-1">
            <li>H1: ${data.structure.h1 ?? 0}</li>
            <li>H2: ${data.structure.h2 ?? 0}</li>
            <li>본문 단락 수: ${data.structure.paragraphs ?? 0}</li>
        </ul>
    </div>` : ''}

    ${Array.isArray(data.problems) ? `
    <div class="mt-3">
        <h4 class="font-semibold text-sm text-red-600 mb-1">문제점</h4>
        <ul class="list-disc ml-5 text-xs space-y-1">
            ${data.problems.map(v => `<li>${v}</li>`).join('')}
        </ul>
    </div>` : ''}

    ${Array.isArray(data.suggestions) ? `
    <div class="mt-3">
        <h4 class="font-semibold text-sm text-green-600 mb-1">개선 제안</h4>
        <ul class="list-disc ml-5 text-xs space-y-1">
            ${data.suggestions.map(v => `<li>${v}</li>`).join('')}
        </ul>
    </div>` : ''}
</div>
`;
                    } else {
                        // 예상 구조가 아닐 경우 raw JSON 출력
                        box.textContent = JSON.stringify(data, null, 2);
                    }
                })
                .catch(() => {
                    showError('SEO 분석 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    setLoading(btn, false);
                    setAiStatus('대기 중');
                });
        };

        /* -----------------------------
           4) 태그 자동 생성
        ----------------------------- */
        document.getElementById('btn-generate-tags').onclick = function () {
            clearError();
            const payload = getPayload();
            const btn = this;

            if (!payload.title || !payload.html) {
                showError('제목과 본문이 있어야 태그를 생성할 수 있습니다.');
                return;
            }

            setLoading(btn, true, '태그 생성 중...');
            setAiStatus('태그 생성 중...');

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
                        span.className = "px-3 py-1 bg-blue-100 text-blue-700 rounded-full cursor-pointer";
                        span.textContent = t;
                        box.appendChild(span);
                    });

                    if (tags.length > 0) {
                        wrap.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    showError('태그 생성 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    setLoading(btn, false);
                    setAiStatus('대기 중');
                });
        };

        /* -----------------------------
           5) 썸네일 생성
        ----------------------------- */
        document.getElementById('btn-generate-thumb').onclick = function () {
            clearError();
            const payload = getPayload();
            const btn = this;

            if (!payload.title) {
                showError('제목을 먼저 입력해주세요. 썸네일 프롬프트에 사용됩니다.');
                return;
            }

            setLoading(btn, true, '썸네일 생성 중...');
            setAiStatus('썸네일 생성 중...');

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
                    if (data.thumbnail) {
                        box.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    showError('썸네일 생성 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    setLoading(btn, false);
                    setAiStatus('대기 중');
                });
        };

        /* -----------------------------
           6) Draft 저장 (모바일/공통)
        ----------------------------- */
        function saveDraft() {
            clearError();
            const payload = getPayload();

            if (!payload.title || !payload.html) {
                showError('제목과 본문을 먼저 생성/입력해주세요.');
                return;
            }

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
                })
                .catch(() => {
                    showError('Draft 저장 중 오류가 발생했습니다.');
                });
        }

        document.getElementById('btn-save-draft').onclick = saveDraft;
        const btnDraftDesktop = document.getElementById('btn-save-draft-desktop');
        if (btnDraftDesktop) btnDraftDesktop.onclick = saveDraft;

        /* -----------------------------
           7) 게시글로 저장 (모바일/공통)
        ----------------------------- */
        function savePost() {
            clearError();
            const payload = getPayload();

            if (!payload.title || !payload.html) {
                showError('제목과 본문을 먼저 생성/입력해주세요.');
                return;
            }

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
                })
                .catch(() => {
                    showError('게시글 저장 중 오류가 발생했습니다.');
                });
        }

        document.getElementById('btn-save-post').onclick = savePost;
        const btnPostDesktop = document.getElementById('btn-save-post-desktop');
        if (btnPostDesktop) btnPostDesktop.onclick = savePost;
    </script>
@endsection
