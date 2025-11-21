@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">{{ $project->name }} - 통계</h1>

    {{-- 기본 정보 --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="p-4 bg-white border rounded shadow">
            <h3 class="font-bold text-lg">총 게시글</h3>
            <p class="text-3xl mt-2">{{ $totalPosts }}</p>
        </div>

        <div class="p-4 bg-white border rounded shadow">
            <h3 class="font-bold text-lg">최근 생성일</h3>
            <p class="text-xl mt-2">{{ $latestDate }}</p>
        </div>

        <div class="p-4 bg-white border rounded shadow">
            <h3 class="font-bold text-lg">평균 SEO 점수</h3>
            <p class="text-3xl mt-2">
                {{ $avgSeoScore ? round($avgSeoScore, 1) : '데이터 없음' }}
            </p>
        </div>
    </div>

    {{-- 키워드 TOP 10 --}}
    <div class="p-4 bg-white border rounded shadow mb-6">
        <h3 class="font-bold text-lg mb-3">키워드 TOP 10</h3>
        <ul class="list-disc ml-6">
            @foreach($topKeywords as $keyword => $count)
                <li>{{ $keyword }} ({{ $count }}건)</li>
            @endforeach
        </ul>
    </div>

    {{-- 월별 생성량 --}}
    <div class="p-4 bg-white border rounded shadow">
        <h3 class="font-bold text-lg mb-3">월별 생성량</h3>
        <canvas id="monthChart" height="140"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthLabels = @json($monthly->keys());
const monthValues = @json($monthly->values());

new Chart(document.getElementById('monthChart'), {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: "게시글 수",
            data: monthValues,
            borderColor: "#4F46E5",
            backgroundColor: "rgba(79, 70, 229, 0.3)",
            fill: true,
            tension: 0.3
        }]
    }
});
</script>

@endsection
