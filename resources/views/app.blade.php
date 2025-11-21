<!doctype html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>BlogGPT</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<nav class="bg-white p-4 shadow">
    <div class="container mx-auto"> <a href="/">BlogGPT</a> </div>
</nav>
<main class="py-6">@yield('content')</main>
<script src="/js/app.js"></script>
</body>
</html>
