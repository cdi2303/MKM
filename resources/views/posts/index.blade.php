@extends('layouts.app')
@section('content')

<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">Posts</h1>

    {{-- 프로젝트 필터 --}}
    <div class="mb-4 flex gap-2">
        <a href="/posts" class="px-3 py-1 rounded bg-gray-300">전체</a>

        @foreach($projects as $p)
            <a href="/posts?project_id={{ $p->id }}" 
               class="px-3 py-1 rounded bg-blue-300">
               {{ $p->name }}
            </a>
        @endforeach
    </div>

    {{-- 글 목록 --}}
    <div class="grid grid-cols-1 gap-4">
        @foreach($posts as $post)
        <div class="p-4 border rounded shadow">
            <h2 class="font-bold text-lg">
                <a href="/posts/{{ $post->id }}">{{ $post->title }}</a>
            </h2>

            <p class="text-sm text-gray-600">
                {{ $post->keyword }}
            </p>
        </div>
        @endforeach
    </div>

</div>

@endsection
