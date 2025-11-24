<x-app-layout>
    <div class="max-w-3xl mx-auto py-6">

        <h1 class="text-2xl font-bold mb-4">프로젝트 생성</h1>

        <form method="POST" action="/projects/store" class="space-y-4">
            @csrf

            <div>
                <label class="block font-semibold mb-1">프로젝트 이름</label>
                <input type="text" name="name" class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-semibold mb-1">설명</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3"></textarea>
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                생성하기
            </button>
        </form>

    </div>
</x-app-layout>
