/* ========== 1) SEO 분석 ========== */
document.getElementById('seoAnalyzeBtn')?.addEventListener('click', () => {

    const title = window.POST_DATA.title;
    const html  = window.POST_DATA.html;
    const keyword = window.POST_DATA.keyword ?? '';

    fetch(window.ROUTES.generateAnalyze, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        },
        body: JSON.stringify({ title, html, keyword })
    })
    .then(r => r.json())
    .then(data => {

        const box = document.getElementById('seoResult');
        box.classList.remove('hidden');

        box.innerHTML = `
<h2 class="text-xl font-bold">SEO 분석 결과</h2>
<p class="mt-2"><strong>점수:</strong> ${data.score}</p>
<p><strong>가독성:</strong> ${data.readability}</p>
<p><strong>키워드 사용:</strong> ${data.keyword_usage}</p>

<h3 class="mt-4 font-bold">구조 분석</h3>
<ul class="ml-4 list-disc">
<li>H1: ${data.structure.h1}</li>
<li>H2: ${data.structure.h2}</li>
<li>본문 단락 수: ${data.structure.paragraphs}</li>
</ul>

<h3 class="mt-4 font-bold text-red-600">문제점</h3>
<ul class="ml-4 list-disc">
${data.problems.map(v => `<li>${v}</li>`).join('')}
</ul>

<h3 class="mt-4 font-bold text-green-600">개선 제안</h3>
<ul class="ml-4 list-disc">
${data.suggestions.map(v => `<li>${v}</li>`).join('')}
</ul>
`;
    });
});


/* ========== 2) SEO 자동 개선 ========== */
document.getElementById('upgradeContentBtn')?.addEventListener('click', () => {

    const title = window.POST_DATA.title;
    const html  = window.POST_DATA.html;
    const keyword = window.POST_DATA.keyword;

    fetch(window.ROUTES.generateUpgrade, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        },
        body: JSON.stringify({ title, html, keyword })
    })
    .then(r => r.json())
    .then(data => {

        Swal.fire({
            title: '개선된 콘텐츠 확인',
            html: `
<div class="text-left">
    <h3 class="font-bold mb-2">🔧 변경된 사항</h3>
    <ul class="list-disc ml-6">
        ${data.changes.map(v => `<li>${v}</li>`).join('')}
    </ul>

    <h3 class="font-bold mt-4 mb-2">📄 개선된 본문</h3>
    <div class="p-3 border rounded bg-gray-50" style="max-height: 400px; overflow-y: auto;">
        ${data.html}
    </div>

    <h3 class="font-bold mt-4 mb-2">🆚 변경 비교</h3>
    <div class="p-3 border rounded bg-gray-50">
        ${data.diff}
    </div>
</div>
`,
            showCancelButton: true,
            confirmButtonText: '본문에 반영하기',
            cancelButtonText: '닫기'
        }).then(result => {

            if (result.isConfirmed) {

                fetch(window.ROUTES.savePost, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": window.CSRF
                    },
                    body: JSON.stringify({
                        id: window.POST_DATA.id,
                        html: data.html,
                        title: window.POST_DATA.title,
                        keyword: window.POST_DATA.keyword
                    })
                }).then(() => location.reload());
            }
        });

    });
});


/* ========== 3) 태그 생성 ========== */
document.getElementById('generateTagsBtn')?.addEventListener('click', () => {

    const title = window.POST_DATA.title;
    const keyword = window.POST_DATA.keyword;
    const html = window.POST_DATA.html;

    fetch(window.ROUTES.generateTags, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        },
        body: JSON.stringify({ title, keyword, html })
    })
    .then(r => r.json())
    .then(data => {

        let tags = data.tags ?? [];

        const tagBox = document.getElementById('tagList');
        tagBox.innerHTML = '';

        tags.forEach(tag => {
            tagBox.innerHTML += `
<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">${tag}</span>
`;
        });

        fetch(window.ROUTES.savePost, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": window.CSRF
            },
            body: JSON.stringify({
                id: window.POST_DATA.id,
                html: window.POST_DATA.html,
                title: window.POST_DATA.title,
                keyword: window.POST_DATA.keyword,
                tags: tags
            })
        });

    });

});


/* ========== 4) 내부 링크 추천 ========== */
document.getElementById('internalLinkBtn')?.addEventListener('click', () => {

    const html = window.POST_DATA.html;
    const keyword = window.POST_DATA.keyword;

    fetch(window.ROUTES.generateInternalLinks, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        },
        body: JSON.stringify({
            project_id: window.POST_DATA.project_id,
            post_id: window.POST_DATA.id,
            html,
            keyword
        })
    })
    .then(res => res.json())
    .then(data => {

        const box = document.getElementById("internalLinkBox");
        const list = document.getElementById("internalLinkList");

        box.classList.remove("hidden");
        list.innerHTML = "";

        data.links.forEach(item => {
            list.innerHTML += `
<li>
    <a href="/posts/${item.id}" class="text-blue-600 underline">
        ${item.title} (${item.keyword})
    </a>
</li>
`;
        });
    });
});


/* ========== 5) 제목 AB 테스트 생성 ========== */
window.generateABTitles = function () {

    fetch(window.POST_DATA.generateTitleUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        }
    })
    .then(r => r.json())
    .then(d => {
        alert("테스트용 제목 5개 생성 완료!");
        console.log(d.titles);
    });
};


/* ========== 6) 품질 진단 ========== */
document.getElementById('qualityCheckBtn')?.addEventListener('click', () => {

    const title = window.POST_DATA.title;
    const html  = window.POST_DATA.html;
    const keyword = window.POST_DATA.keyword;

    fetch(window.ROUTES.qualityCheck, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": window.CSRF
        },
        body: JSON.stringify({ title, html, keyword })
    })
    .then(r => r.json())
    .then(data => {

        const box = document.getElementById('qualityBox');
        box.classList.remove('hidden');

        box.innerHTML = `
<h2 class="text-xl font-bold mb-3">🧪 콘텐츠 품질 진단 결과</h2>

<p><strong>스팸 위험도:</strong> ${data.spam_risk}%</p>
<p><strong>AI 감지 위험도:</strong> ${data.ai_detect_risk}%</p>
<p><strong>가독성 평가:</strong> ${data.readability}</p>
<p><strong>키워드 분석:</strong> ${data.keyword_density}</p>

<h3 class="font-bold mt-4">개선 포인트</h3>
<ul class="list-disc ml-6">
${data.suggestions.map(v => `<li>${v}</li>`).join('')}
</ul>
`;
    });

});


/* ========== 7) CTR 차트 ========== */
if (document.getElementById('ctrChart')) {
    const ctrValue = window.POST_DATA.ctr ?? 0;

    new Chart(document.getElementById('ctrChart'), {
        type: 'doughnut',
        data: {
            labels: ['CTR', 'Remaining'],
            datasets: [{
                data: [ctrValue, Math.max(100 - ctrValue, 0)],
            }]
        },
        options: {
            cutout: '70%',
        }
    });
}
