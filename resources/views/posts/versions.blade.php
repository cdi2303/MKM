@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-4">
            버전 히스토리 - {{ $post->title }}
        </h1>

        <a href="/posts/{{ $post->id }}" class="text-blue-600 underline mb-4 block">
            ← 글 상세로 돌아가기
        </a>

        @if($versions->count() === 0)
            <p class="text-gray-500">아직 생성된 버전이 없습니다.</p>
        @else
            <div class="space-y-4">
                @foreach($versions as $ver)
                    <div class="p-4 border rounded bg-white shadow">

                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="font-bold text-lg">
                                    버전 {{ $ver->version }}
                                </h2>
                                <p class="text-gray-500 text-sm">
                                    {{ $ver->created_at->format('Y-m-d H:i') }}
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <a href="/posts/{{ $post->id }}/versions/{{ $ver->version }}"
                                   class="px-3 py-2 bg-blue-600 text-white rounded text-sm">
                                    상세 보기
                                </a>

                                <form method="POST" action="/posts/{{ $post->id }}/versions/{{ $ver->version }}/restore">
                                    @csrf
                                    <button class="px-3 py-2 bg-green-600 text-white rounded text-sm"
                                            onclick="return confirm('해당 버전으로 복원할까요?')">
                                        복원
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-3 text-gray-700 line-clamp-3 prose max-w-none">
                            {!! Str::limit($ver->content, 120) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection
