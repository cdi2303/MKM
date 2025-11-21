@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">{{ $project->name }}</h1>

    <p class="mb-4 text-gray-600">{{ $project->description }}</p>

    <div class="mb-6 flex gap-3">
        <a href="/projects/{{ $project->id }}/stats"
           class="px-4 py-2 bg-blue-600 text-white rounded">
            📊 통계 페이지
        </a>

        <a href="/generate"
           class="px-4 py-2 bg-green-600 text-white rounded">
            ✨ 글 생성하기
        </a>
    </div>

    <h2 class="text-xl font-bold mb-3">프로젝트 게시글 목록</h2>

    @foreach($project->posts as $post)
        <a href="/posts/{{ $post->id }}"
           class="block border p-3 mb-2 bg-white rounded hover:bg-gray-100">
            <div class="font-bold">{{ $post->title }}</div>
            <div class="text-sm text-gray-500">{{ $post->created_at }}</div>
        </a>
    @endforeach

</div>
@endsection
