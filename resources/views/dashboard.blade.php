@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-8">

        {{-- 상단 인사 + 주요 액션 --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-1">대시보드</h1>
                <p class="text-gray-500 text-sm">
                    프로젝트 현황, SEO 상태, 최근 생성된 글을 한눈에 확인하세요.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('generate.page') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    ✍️ 새 글 생성하기
                </a>
                <a href="/projects"
                   class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
                    📂 프로젝트 목록
                </a>
            </div>
        </div>

        {{-- 주요 숫자 카드 --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-4 bg-white border rounded-xl shadow">
                <h3 class="text-sm text-gray-500">프로젝트 수</h3>
                <p class="text-3xl font-bold mt-2">{{ $projectsCount }}</p>
            </div>

            <div class="p-4 bg-white border rounded-xl shadow">
                <h3 class="text-sm text-gray-500">발행 글 수</h3>
                <p class="text-3xl font-bold mt-2">{{ $postsCount }}</p>
            </div>

            <div class="p-4 bg-white border rounded-xl shadow">
                <h3 class="text-sm text-gray-500">Draft 수</h3>
                <p class="text-3xl font-bold mt-2">{{ $draftsCount }}</p>
            </div>

            <div class="p-4 bg-white border rounded-xl shadow">
                <h3 class="text-sm text-gray-500">평균 SEO 점수</h3>
                <p class="text-3xl font-bold mt-2">
                    {{ $avgSeoScore !== null ? $avgSeoScore : '-' }}
                </p>
            </div>
        </div>

        {{-- 최근 생성된 글 5개 --}}
        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">최근 발행한 글</h2>
                <a href="/posts" class="text-sm text-blue-600 underline">전체 글 보기</a>
            </div>

            @if($recentPosts->isEmpty())
                <p class="text-gray-500 text-sm">아직 발행된 글이 없습니다.</p>
            @else
                <ul class="divide-y">
                    @foreach($recentPosts as $post)
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <a href="/posts/{{ $post->id }}" class="font-semibold hover:underline">
                                    {{ $post->title }}
                                </a>
                                <div class="text-xs text-gray-500 mt-1">
                                    프로젝트: {{ $post->project->name ?? '-' }} |
                                    키워드: {{ $post->keyword }} |
                                    {{ $post->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>
                            <div class="text-right text-xs text-gray-500">
                                @if(isset($post->meta['seo_score']))
                                    <div>SEO: <b>{{ $post->meta['seo_score'] }}</b></div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- 프로젝트별 카드 + SEO/통계/클러스터 링크 --}}
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-bold mb-4">프로젝트별 현황</h2>

            @if($projects->isEmpty())
                <p class="text-gray-500 text-sm">프로젝트가 없습니다. 먼저 프로젝트를 생성해 주세요.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                        <div class="border rounded-xl p-4 hover:shadow transition">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold">{{ $project->name }}</h3>
                                <span class="text-xs text-gray-500">
                                글 {{ $project->posts_count }}개
                            </span>
                            </div>
                            @if($project->description)
                                <p class="text-xs text-gray-500 mb-3">
                                    {{ \Illuminate\Support\Str::limit($project->description, 60) }}
                                </p>
                            @endif

                            <div class="flex flex-wrap gap-2 text-xs">
                                <a href="/projects/{{ $project->id }}"
                                   class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">
                                    상세
                                </a>
                                <a href="/projects/{{ $project->id }}/stats"
                                   class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                    통계
                                </a>
                                <a href="{{ route('projects.seo', $project->id) }}"
                                   class="px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                                    SEO 대시보드
                                </a>
                                <a href="{{ route('projects.cluster', $project->id) }}"
                                   class="px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                    키워드 클러스터
                                </a>
                                <a href="{{ route('projects.seo.pdf', $project->id) }}"
                                   class="px-2 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200">
                                    SEO 리포트 PDF
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection
