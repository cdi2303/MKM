@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-8">Draft 목록</h1>

        @if ($drafts->count() === 0)
            <p class="text-gray-500">아직 Draft가 없습니다.</p>
        @else
            <div class="bg-white border rounded-xl shadow p-6">
                <table class="min-w-full">
                    <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">제목</th>
                        <th class="px-4 py-2 text-left">키워드</th>
                        <th class="px-4 py-2 text-left">생성일</th>
                        <th class="px-4 py-2 text-left">작업</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($drafts as $draft)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $draft->title }}</td>
                            <td class="px-4 py-2">{{ $draft->keyword }}</td>
                            <td class="px-4 py-2">{{ $draft->created_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 flex gap-3">
                                <a href="{{ url('/drafts/'.$draft->id) }}"
                                   class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                                    편집
                                </a>

                                <form action="{{ url('/drafts/'.$draft->id.'/publish') }}" method="POST">
                                    @csrf
                                    <button class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                        게시글로 변환
                                    </button>
                                </form>

                                <form action="{{ url('/drafts/'.$draft->id.'/delete') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                        삭제
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>
        @endif

    </div>
@endsection
