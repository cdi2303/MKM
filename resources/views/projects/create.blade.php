@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Create Project</h1>

    <form method="POST" action="/projects/store">
        @csrf
        <input name="name" class="w-full border p-2 rounded mb-2" placeholder="Project Name" />
        <textarea name="description" class="w-full border p-2 rounded mb-2" placeholder="Description"></textarea>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
