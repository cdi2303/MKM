<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
            font-size: 12px;
            color: #333;
        }
        h1,h2,h3 {
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #555;
        }
        .section {
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>SEO 분석 보고서</h1>
    <h3>{{ $project->name }}</h3>
</div>

<div class="section">
    <h2>1. 프로젝트 개요</h2>
    <p>프로젝트명: <strong>{{ $project->name }}</strong></p>
    <p>총 게시글 수: {{ $project->posts->count() }}</p>
</div>

<div class="section">
    <h2>2. AI 자동 생성 SEO 분석 리포트</h2>
    <p>{!! nl2br(e($report)) !!}</p>
</div>

<div class="section">
    <h2>3. 게시글별 SEO 점수</h2>

    <table class="table">
        <thead>
        <tr>
            <th>제목</th>
            <th>키워드</th>
            <th>SEO 점수</th>
        </tr>
        </thead>
        <tbody>
        @foreach($posts as $p)
            <tr>
                <td>{{ $p->title }}</td>
                <td>{{ $p->keyword }}</td>
                <td>{{ $p->meta['seo_score'] ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    본 리포트는 AI 기반 자동 분석 도구 MKM 시스템에서 생성되었습니다.
</div>

</body>
</html>
