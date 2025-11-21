@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-4">임시 저장된 글</h1>

    @foreach($drafts as $draft)
        <a href="/drafts/{{ $draft->id }}"
           class="block border p-3 mb-2 rounded bg-white hover:bg-gray-100">
            <div class="font-bold">{{ $draft->title ?: '(제목 없음)' }}</div>
            <div class="text-sm text-gray-600">{{ $draft->created_at }}</div>
        </a>
    @endforeach

</div>
@endsection
