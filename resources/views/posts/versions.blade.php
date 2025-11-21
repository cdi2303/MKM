@extends('layouts.app')
@section('content')

<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">
        {{ $post->title }} - 버전 히스토리
    </h1>

    @foreach($versions as $v)
        <a href="/posts/{{ $post->id }}/versions/{{ $v->version }}"
           class="block border p-3 bg-white rounded mb-2 hover:bg-gray-100">
            <div class="font-bold">버전 {{ $v->version }}</div>
            <div class="text-sm text-gray-500">
                {{ $v->created_at }}
            </div>
        </a>
    @endforeach

</div>

@endsection
