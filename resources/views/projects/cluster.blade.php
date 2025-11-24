@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">

        <h1 class="text-3xl font-bold mb-6">
            키워드 클러스터 — {{ $project->name }}
        </h1>

        <p class="text-gray-500 mb-4">
            프로젝트 내 글들의 키워드·내용 기반 유사도 네트워크입니다.
        </p>

        <button id="runCluster"
                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 mb-6">
            🔍 클러스터 분석 실행
        </button>

        <div id="clusterArea" class="border rounded p-4 bg-white" style="height:600px;">
            <p class="text-gray-400">클러스터 분석을 실행하면 그래프가 여기에 표시됩니다.</p>
        </div>

    </div>

    <script src="https://d3js.org/d3.v7.min.js"></script>

    <script>
        document.getElementById("runCluster").onclick = function () {

            fetch("{{ route('projects.cluster.generate', $project->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
                .then(r => r.json())
                .then(data => renderCluster(data));
        };


        function renderCluster(data) {

            document.getElementById("clusterArea").innerHTML = "";

            const width  = document.getElementById("clusterArea").clientWidth;
            const height = document.getElementById("clusterArea").clientHeight;

            const svg = d3.select("#clusterArea")
                .append("svg")
                .attr("width", width)
                .attr("height", height);

            // 그래프 레이아웃
            const simulation = d3.forceSimulation(data.nodes)
                .force("link", d3.forceLink(data.links).id(d => d.id).distance(150))
                .force("charge", d3.forceManyBody().strength(-400))
                .force("center", d3.forceCenter(width / 2, height / 2));

            // 링크
            const link = svg.append("g")
                .attr("stroke", "#aaa")
                .selectAll("line")
                .data(data.links)
                .enter()
                .append("line")
                .attr("stroke-width", d => 1 + d.score * 3);

            // 노드
            const node = svg.append("g")
                .selectAll("circle")
                .data(data.nodes)
                .enter()
                .append("circle")
                .attr("r", 14)
                .attr("fill", "#4f46e5")
                .call(drag(simulation))
                .on("click", (e, d) => {
                    window.location.href = "/posts/" + d.id;
                });

            // 라벨
            const label = svg.append("g")
                .selectAll("text")
                .data(data.nodes)
                .enter()
                .append("text")
                .text(d => d.keyword)
                .attr("font-size", "10px")
                .attr("fill", "#333");

            simulation.on("tick", () => {
                link
                    .attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                node
                    .attr("cx", d => d.x)
                    .attr("cy", d => d.y);

                label
                    .attr("x", d => d.x + 16)
                    .attr("y", d => d.y + 4);
            });


            function drag(sim) {
                return d3.drag()
                    .on("start", (e, d) => {
                        if (!e.active) sim.alphaTarget(0.3).restart();
                        d.fx = d.x;
                        d.fy = d.y;
                    })
                    .on("drag", (e, d) => {
                        d.fx = e.x;
                        d.fy = e.y;
                    })
                    .on("end", (e, d) => {
                        if (!e.active) sim.alphaTarget(0);
                        d.fx = null;
                        d.fy = null;
                    });
            }
        }
    </script>

@endsection
