@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        {{-- 상단 헤더 --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
                <p class="text-gray-600">{{ $project->description }}</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ url('/projects/'.$project->id.'/edit') }}"
                   class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    수정
                </a>

                <a href="{{ route('projects.stats', $project->id) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    통계 보기
                </a>
            </div>
        </div>

        {{-- 게시글 리스트 --}}
        <div class="bg-white border rounded-xl shadow p-6">
            <h2 class="text-xl font-bold mb-4">게시글 목록</h2>

            @if ($posts->count() === 0)
                <p class="text-gray-500">아직 게시글이 없습니다.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">제목</th>
                            <th class="px-4 py-2 text-left">키워드</th>
                            <th class="px-4 py-2 text-left">SEO</th>
                            <th class="px-4 py-2 text-left">날짜</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($posts as $post)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $post->id }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ url('/posts/'.$post->id) }}"
                                       class="text-indigo-600 hover:underline">
                                        {{ $post->title }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $post->keyword }}</td>
                                <td class="px-4 py-2">
                                    @if(!empty($post->meta['score']))
                                        {{ $post->meta['score'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    {{ $post->created_at->format('Y-m-d') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

    </div>
@endsection
