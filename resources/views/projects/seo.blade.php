@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-6">
            프로젝트 SEO 대시보드 - {{ $project->name }}
        </h1>


        {{-- SEO 점수 변화 그래프 --}}
        <div class="bg-white p-6 rounded shadow mb-10">
            <h2 class="text-2xl font-bold mb-4">📈 SEO 점수 변화</h2>
            <canvas id="seoTrendChart" height="100"></canvas>
        </div>


        {{-- 최고/최저 점수 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

            {{-- 최고 --}}
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-xl font-bold">🥇 최고 SEO 점수 게시글</h3>
                @if($best)
                    <p class="mt-2 font-bold">{{ $best->title }}</p>
                    <p class="text-gray-500">점수: {{ $best->meta['seo_score'] }}</p>
                    <a href="/posts/{{ $best->id }}" class="text-blue-600 underline">보러가기</a>
                @else
                    <p class="text-gray-500">데이터 없음</p>
                @endif
            </div>


            {{-- 최저 --}}
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-xl font-bold">🥉 최저 SEO 점수 게시글</h3>
                @if($worst)
                    <p class="mt-2 font-bold">{{ $worst->title }}</p>
                    <p class="text-gray-500">점수: {{ $worst->meta['seo_score'] }}</p>
                    <a href="/posts/{{ $worst->id }}" class="text-blue-600 underline">보러가기</a>
                @else
                    <p class="text-gray-500">데이터 없음</p>
                @endif
            </div>

        </div>



        {{-- 키워드 평균 SEO 점수 --}}
        <div class="bg-white p-6 rounded shadow mb-10">
            <h2 class="text-2xl font-bold mb-4">🔑 키워드별 평균 SEO 점수</h2>

            @if($keywordScores->count() > 0)
                <canvas id="keywordSeoChart" height="100"></canvas>
            @else
                <p class="text-gray-500">키워드 데이터 없음</p>
            @endif
        </div>


    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        /* SEO 점수 변화 */
        new Chart(document.getElementById('seoTrendChart'), {
            type: 'line',
            data: {
                labels: @json($posts->pluck('title')),
                datasets: [{
                    label: 'SEO 점수',
                    data: @json($scores),
                    borderWidth: 2,
                    tension: 0.3
                }]
            }
        });

        /* 키워드별 평균 점수 */
        @if($keywordScores->count() > 0)
        new Chart(document.getElementById('keywordSeoChart'), {
            type: 'bar',
            data: {
                labels: @json($keywordScores->keys()),
                datasets: [{
                    label: '평균 SEO 점수',
                    data: @json($keywordScores->values()),
                    borderWidth: 1
                }]
            }
        });
        @endif
    </script>

@endsection
