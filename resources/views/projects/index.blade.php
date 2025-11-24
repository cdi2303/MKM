@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        {{-- 상단 타이틀 + 생성 버튼 --}}
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">내 프로젝트</h1>
            <a href="{{ route('projects.create') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700">
                + 새 프로젝트
            </a>
        </div>

        {{-- 프로젝트 없을 때 --}}
        @if ($projects->count() === 0)
            <div class="text-center text-gray-500 py-20">
                아직 생성된 프로젝트가 없습니다.<br>
                <a href="{{ route('projects.create') }}" class="text-indigo-600 underline">프로젝트 만들기</a>
            </div>
        @endif

        {{-- 프로젝트 카드 리스트 --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($projects as $project)
                <a href="{{ url('/projects/'.$project->id) }}"
                   class="block p-6 bg-white border rounded-xl shadow hover:shadow-lg transition">

                    <h2 class="text-xl font-semibold mb-2">{{ $project->name }}</h2>

                    <p class="text-gray-500 text-sm mb-4">
                        {{ $project->description ?? '설명 없음' }}
                    </p>

                    {{-- 간단 통계 --}}
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>게시글: {{ $project->posts()->count() }}</span>
                        <span>
                    최신:
                    @php
                        $latest = $project->posts()->latest()->first();
                        echo $latest ? $latest->created_at->format('Y-m-d') : '-';
                    @endphp
                </span>
                    </div>

                </a>
            @endforeach
        </div>

    </div>
@endsection
