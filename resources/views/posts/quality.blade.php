@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-6">

        <h1 class="text-3xl font-bold mb-4">
            품질 분석 — {{ $post->title }}
        </h1>

        <div class="flex gap-3 mb-6">
            <button id="btnAnalyze"
                    class="px-4 py-2 bg-indigo-600 text-white rounded">
                🔍 품질 분석 실행
            </button>

            <button id="btnRewrite"
                    class="px-4 py-2 bg-green-600 text-white rounded">
                ✨ 자동 품질 개선(Rewrite)
            </button>
        </div>

        {{-- 점수 차트 --}}
        <canvas id="scoreChart" height="100"></canvas>

        {{-- 문제점 --}}
        <div id="problemsBox" class="hidden bg-red-50 p-4 border rounded"></div>

        {{-- 개선안 --}}
        <div id="suggestionsBox" class="hidden bg-green-50 p-4 border rounded"></div>

        {{-- 리라이트 출력 --}}
        <div id="rewriteBox" class="hidden bg-white p-4 border rounded"></div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const csrf = '{{ csrf_token() }}';
        const postId = {{ $post->id }};

        document.getElementById("btnAnalyze").onclick = () => {
            fetch(`/posts/${postId}/quality/analyze`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                }
            })
                .then(r => r.json())
                .then(data => {
                    // 차트 출력
                    new Chart(document.getElementById("scoreChart"), {
                        type: 'bar',
                        data: {
                            labels: ['SEO', '가독성', '정보밀도', '중복감점', '키워드'],
                            datasets: [{
                                label: '점수',
                                data: [
                                    data.scores.seo,
                                    data.scores.readability,
                                    data.scores.density,
                                    data.scores.redundancy,
                                    data.scores.keyword
                                ],
                                backgroundColor: '#6366f1'
                            }]
                        }
                    });

                    document.getElementById("problemsBox").classList.remove("hidden");
                    document.getElementById("problemsBox").innerHTML =
                        `<h3 class='font-bold mb-2'>문제점</h3><ul class='list-disc ml-6'>` +
                        data.problems.map(v => `<li>${v}</li>`).join('') + `</ul>`;

                    document.getElementById("suggestionsBox").classList.remove("hidden");
                    document.getElementById("suggestionsBox").innerHTML =
                        `<h3 class='font-bold mb-2'>개선안</h3><ul class='list-disc ml-6'>` +
                        data.suggestions.map(v => `<li>${v}</li>`).join('') + `</ul>`;
                });
        };

        document.getElementById("btnRewrite").onclick = () => {
            fetch(`/posts/${postId}/quality/rewrite`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                }
            })
                .then(r => r.json())
                .then(data => {

                    document.getElementById("rewriteBox").classList.remove("hidden");
                    document.getElementById("rewriteBox").innerHTML = `
            <h3 class="font-bold mb-4">✨ 개선된 HTML</h3>
            <div class="p-3 border rounded mb-6">${data.html}</div>

            <h3 class="font-bold mb-2">📝 변경된 부분(DIFF)</h3>
            <div class="p-3 border rounded bg-gray-50">${data.diff}</div>
        `;
                });
        };
    </script>
@endsection
