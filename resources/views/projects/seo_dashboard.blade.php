@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-8">

        <h1 class="text-3xl font-bold mb-4">
            SEO 대시보드 — {{ $project->name }}
        </h1>

        {{-- 평균 점수 --}}
        <div class="p-6 bg-white rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-2">프로젝트 평균 SEO 점수</h2>

            <p class="text-5xl font-bold text-indigo-700">
                {{ $avgScore !== null ? $avgScore : '-' }}
            </p>
        </div>

        {{-- SEO 점수 차트 --}}
        <div class="p-6 bg-white rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4">글별 SEO 점수</h2>

            <canvas id="seoChart" height="100"></canvas>
        </div>

        {{-- 키워드 그룹 --}}
        <div class="p-6 bg-white rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4">키워드 클러스터 통계</h2>

            <table class="w-full border-collapse">
                <thead>
                <tr class="border-b">
                    <th class="p-2 text-left">키워드</th>
                    <th class="p-2 text-left">글 수</th>
                    <th class="p-2 text-left">평균 SEO 점수</th>
                </tr>
                </thead>
                <tbody>
                @foreach($keywordStats as $keyword => $stat)
                    <tr class="border-b">
                        <td class="p-2">{{ $keyword }}</td>
                        <td class="p-2">{{ $stat['count'] }}</td>
                        <td class="p-2">{{ $stat['avg_score'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- AI 분석 --}}
        <div class="p-6 bg-white rounded-xl shadow border">
            <h2 class="text-xl font-bold mb-4">AI SEO 종합 분석</h2>

            <div class="whitespace-pre-line leading-7 text-gray-700">
                {!! nl2br(e($aiReport)) !!}
            </div>
        </div>

    </div>


    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('seoChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($posts->pluck('title')) !!},
                datasets: [{
                    label: 'SEO 점수',
                    data: {!! json_encode($posts->map(fn($p) => $p->meta['seo_score'] ?? null)) !!},
                    borderWidth: 1,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>

@endsection
