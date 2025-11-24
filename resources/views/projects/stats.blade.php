@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-6">
            프로젝트 통계 - {{ $project->name }}
        </h1>

        {{-- 카드 섹션 --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

            {{-- 총 게시글 --}}
            <div class="p-4 bg-white border rounded shadow">
                <h3 class="font-bold text-lg">총 게시글</h3>
                <p class="text-4xl mt-2 font-bold">{{ $totalPosts }}</p>
            </div>

            {{-- 최근 생성일 --}}
            <div class="p-4 bg-white border rounded shadow">
                <h3 class="font-bold text-lg">최근 생성일</h3>
                <p class="text-xl mt-2">{{ $latestDate }}</p>
            </div>

            {{-- 평균 SEO 점수 --}}
            <div class="p-4 bg-white border rounded shadow">
                <h3 class="font-bold text-lg">평균 SEO 점수</h3>
                <p class="text-4xl mt-2 font-bold">
                    {{ $avgSeoScore ? number_format($avgSeoScore, 1) : '-' }}
                </p>
            </div>

        </div>



        {{-- 월별 게시글 생성 추세 --}}
        <div class="bg-white p-6 rounded shadow mb-10">
            <h2 class="text-2xl font-bold mb-4">📈 월별 게시글 생성 추세</h2>

            <canvas id="monthlyChart" height="100"></canvas>
        </div>



        {{-- 키워드 TOP 10 --}}
        <div class="bg-white p-6 rounded shadow mb-10">
            <h2 class="text-2xl font-bold mb-4">🔑 키워드 TOP 10</h2>

            @if($topKeywords->count() > 0)
                <canvas id="keywordChart" height="100"></canvas>
            @else
                <p class="text-gray-500">키워드 데이터가 없습니다.</p>
            @endif
        </div>



        {{-- 최근 게시글 목록 --}}
        <div class="bg-white p-6 rounded shadow mb-10">
            <h2 class="text-2xl font-bold mb-4">📝 최근 게시글</h2>

            @foreach($project->posts()->orderBy('id','desc')->take(10)->get() as $post)
                <a href="/posts/{{ $post->id }}"
                   class="block p-3 border-b hover:bg-gray-50">
                    <div class="font-bold">{{ $post->title }}</div>
                    <div class="text-sm text-gray-500">{{ $post->created_at }}</div>
                </a>
            @endforeach
        </div>

    </div>




    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        /* -------------------------------------------
           월별 생성 추세 차트
        ------------------------------------------- */
        const monthlyLabels = @json($monthly->keys());
        const monthlyValues = @json($monthly->values());

        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: '게시글 수',
                    data: monthlyValues,
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });


        /* -------------------------------------------
           키워드 TOP 10 차트
        ------------------------------------------- */
        @if($topKeywords->count() > 0)
        new Chart(document.getElementById('keywordChart'), {
            type: 'bar',
            data: {
                labels: @json($topKeywords->keys()),
                datasets: [{
                    label: '사용 빈도',
                    data: @json($topKeywords->values()),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        @endif

    </script>

@endsection
