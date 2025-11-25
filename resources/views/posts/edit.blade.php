@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">게시글 수정</h1>

    <form method="POST" action="{{ route('posts.update', $post->id) }}">
        @csrf
        @method('PUT')

        {{-- 프로젝트 --}}
        <label class="font-semibold">프로젝트</label>
        <select name="project_id" class="w-full border p-2 rounded mb-4">
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ $p->id == $post->project_id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>

        {{-- 제목 --}}
        <label class="font-semibold">제목</label>
        <input name="title" class="w-full border p-2 rounded mb-4"
               value="{{ $post->title }}">

        {{-- 키워드 --}}
        <label class="font-semibold">키워드</label>
        <input name="keyword" class="w-full border p-2 rounded mb-4"
               value="{{ $post->keyword }}">

        {{-- HTML Editor --}}
        <label class="font-semibold">본문 (HTML)</label>
        <textarea id="htmlEditor" name="html" rows="20"
                  class="w-full border p-4 rounded">
@php echo htmlentities($post->html); @endphp
</textarea>

        <button class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded">
            수정 완료
        </button>
    </form>

</div>

{{-- CodeMirror --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    const editor = CodeMirror.fromTextArea(
        document.getElementById('htmlEditor'),
        {
            lineNumbers: true,
            mode: "htmlmixed",
            theme: "default",
            lineWrapping: true
        }
    );
</script>

@endsection
