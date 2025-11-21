@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    {{-- 프로젝트 기본 정보 --}}
    <h1 class="text-3xl font-bold mb-2">
        {{ $project->name }} 통계
    </h1>

    <p class="text-gray-600 mb-6">
        {{ $project->description }}
    </p>

    {{-- 3개 기본 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

        <div class="p-4 bg-white shadow rounded">
            <h2 class="font-bold text-lg">총 글 수</h2>
            <p class="text-3xl mt-2">{{ $totalPosts }}</p>
        </div>

        <div class="p-4 bg-white shadow rounded">
            <h2 class="font-bold text-lg">최근 생성</h2>
            <p class="text-xl mt-2">{{ $latestDate }}</p>
        </div>

        <div class="p-4 bg-white shadow rounded">
            <h2 class="font-bold text-lg">프로젝트 ID</h2>
            <p class="text-xl mt-2">{{ $project->id }}</p>
        </div>

    </div>

    {{-- 키워드 TOP 5 --}}
    <h2 class="text-xl font-bold mb-3">많이 사용된 키워드 TOP 5</h2>

    @forelse($topKeywords as $kw)
        <div class="p-3 border rounded mb-2 bg-gray-50">
            {{ $kw['keyword'] }}
            <span class="text-gray-500">({{ $kw['count'] }}회)</span>
        </div>
    @empty
        <p class="text-gray-500 mb-4">아직 생성된 글이 없습니다.</p>
    @endforelse

    {{-- 최근 글 5개 --}}
    <h2 class="text-xl font-bold mt-8 mb-3">최근 생성된 글 5개</h2>

    @foreach($recentPosts as $post)
        <div class="p-4 border rounded mb-3">
            <a href="/posts/{{ $post->id }}" class="font-bold text-lg">
                {{ $post->title }}
            </a>
            <p class="text-sm text-gray-500">{{ $post->created_at->format('Y-m-d H:i') }}</p>
        </div>
    @endforeach

    <hr class="my-10">

    {{-- ------------------------------
         ⭐ Chart.js 그래프 섹션
       ------------------------------ --}}

    <h2 class="text-xl font-bold mb-4">키워드 사용 빈도 그래프</h2>
    <canvas id="keywordChart" height="120"></canvas>

    <h2 class="text-xl font-bold mt-10 mb-4">최근 30일 글 생성 추이</h2>
    <canvas id="dailyChart" height="120"></canvas>

    <h2 class="text-xl font-bold mt-10 mb-4">전체 생성일 분포</h2>
    <canvas id="dateChart" height="120"></canvas>

</div>
@endsection


{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- 그래프 스크립트 --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    // 🔵 1. 키워드 사용 빈도 그래프
    new Chart(document.getElementById('keywordChart'), {
        type: 'bar',
        data: {
            labels: @json($keywordStats->keys()),
            datasets: [{
                label: '사용 빈도',
                data: @json($keywordStats->values()),
                borderWidth: 1
            }]
        }
    });

    // 🔵 2. 최근 30일 글 생성량
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: @json($dailyStats->keys()),
            datasets: [{
                label: '일별 생성량',
                data: @json($dailyStats->values()),
                borderWidth: 2
            }]
        }
    });

    // 🔵 3. 전체 날짜별 생성량
    new Chart(document.getElementById('dateChart'), {
        type: 'bar',
        data: {
            labels: @json($dateStats->keys()),
            datasets: [{
                label: '생성 수',
                data: @json($dateStats->values()),
                borderWidth: 1
            }]
        }
    });

});
</script>
