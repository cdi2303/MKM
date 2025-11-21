@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Projects</h1>

    <a href="/projects/create" class="bg-blue-600 text-white px-4 py-2 rounded">Create Project</a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        @foreach($projects as $p)
        <div class="p-4 shadow bg-white rounded">
            <h2 class="font-bold text-lg">{{ $p->name }}</h2>
            <p class="text-sm text-gray-600">{{ $p->description }}</p>
            <div class="mt-2">
                <a href="/projects/{{ $p->id }}/edit" class="text-blue-600">Edit</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
