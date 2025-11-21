@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Project</h1>

    <form method="POST" action="/projects/{{ $project->id }}/update">
        @csrf
        <input name="name" value="{{ $project->name }}" class="w-full border p-2 rounded mb-2" />
        <textarea name="description" class="w-full border p-2 rounded mb-2">{{ $project->description }}</textarea>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection
