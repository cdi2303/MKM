<x-app-layout>
    <div class="max-w-3xl mx-auto py-6">

        <h1 class="text-2xl font-bold mb-4">프로젝트 수정</h1>

        <form method="POST" action="/projects/{{ $project->id }}/update" class="space-y-4">
            @csrf

            <div>
                <label class="block font-semibold mb-1">프로젝트 이름</label>
                <input type="text" name="name" value="{{ $project->name }}" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-semibold mb-1">설명</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3">{{ $project->description }}</textarea>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                수정 완료
            </button>
        </form>

    </div>
</x-app-layout>
