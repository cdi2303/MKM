@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-4">
            버전 {{ $ver->version }} 상세 보기
        </h1>

        <a href="/posts/{{ $post->id }}/versions" class="text-blue-600 underline mb-4 block">
            ← 버전 목록으로 돌아가기
        </a>

        <form method="POST" action="/posts/{{ $post->id }}/versions/{{ $ver->version }}/restore">
            @csrf
            <button class="px-4 py-2 bg-green-600 text-white rounded mb-4"
                    onclick="return confirm('이 버전으로 복원할까요?')">
                이 버전으로 복원하기
            </button>
        </form>

        <div class="prose max-w-none bg-white p-4 border rounded shadow">
            {!! $ver->html !!}
        </div>

    </div>
@endsection
