@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">{{ $post->title }}</h1>

        <div class="prose max-w-none bg-white p-6 shadow">
            {!! $post->html !!}
        </div>

        <a href="/posts" class="block mt-4 text-blue-600">← Back to list</a>
    </div>
@endsection
