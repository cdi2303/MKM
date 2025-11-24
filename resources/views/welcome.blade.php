<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>MKM AI Content Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

<div class="min-h-screen flex flex-col justify-center items-center p-6">

    <h1 class="text-4xl font-extrabold text-gray-900 mb-4">
        MKM AI Content Platform
    </h1>

    <p class="text-gray-600 text-lg mb-10 text-center max-w-2xl leading-relaxed">
        AI 기반 SEO 최적화 블로그 자동 생성 시스템
        <br>
        프로젝트, 글 생성, SEO 분석, 클러스터 그래프, PDF 리포트까지 한 번에.
    </p>

    {{-- 로그인 여부에 따라 버튼 분기 --}}
    @auth
        <div class="flex flex-wrap justify-center gap-4">

            <a href="/dashboard"
               class="px-6 py-3 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">
                🚀 대시보드로 이동
            </a>

            <a href="/projects"
               class="px-6 py-3 bg-gray-800 text-white rounded-xl shadow hover:bg-gray-900">
                📂 프로젝트 관리
            </a>

            <a href="/generate"
               class="px-6 py-3 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                ✍️ 새 글 생성하기
            </a>

        </div>
    @endauth

    @guest
        <div class="flex flex-wrap justify-center gap-4">
            <a href="/login"
               class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">
                로그인
            </a>

            <a href="/register"
               class="px-6 py-3 bg-gray-700 text-white rounded-xl shadow hover:bg-gray-800">
                회원가입
            </a>
        </div>
    @endguest

    <footer class="mt-16 text-gray-400 text-sm">
        MKM AI Automation Platform © {{ date('Y') }}
    </footer>

</div>
</body>
</html>
