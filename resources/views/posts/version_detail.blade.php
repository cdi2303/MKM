@extends('layouts.app')
@section('content')

<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">버전 {{ $ver->version }}</h1>
    <p class="text-gray-500">{{ $ver->created_at }}</p>

    <div class="border p-4 my-4 bg-white rounded">
        {!! $ver->html !!}
    </div>

    <form action="/posts/{{ $post->id }}/versions/{{ $ver->version }}/restore" method="POST">
        @csrf
        <button class="px-4 py-2 bg-green-600 rounded text-white">
            이 버전으로 복원하기
        </button>
    </form>

</div>

@endsection
