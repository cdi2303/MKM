<?php

namespace App\Services;

use App\Models\Post;
use GuzzleHttp\Client;

class BlogStatsService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
    }

    /**
     * 전체 Post 대상으로 WordPress / Tistory 통계 수집
     */
    public function collectAll(): void
    {
        Post::whereNotNull('publish_meta')->chunkById(100, function ($posts) {
            foreach ($posts as $post) {
                $this->collectForPost($post);
            }
        });
    }

    public function collectForPost(Post $post): void
    {
        $meta = $post->publish_meta ?? [];

        // 워드프레스
        if (!empty($meta['wordpress']['post_id']) || !empty($meta['wordpress']['url'])) {
            $stats = $this->fetchWordPressStats($meta['wordpress']);
            if ($stats) {
                $post->wp_ctr = $this->calculateCtr($stats['clicks'], $stats['impressions']);
            }
        }

        // 티스토리
        if (!empty($meta['tistory']['post_id']) || !empty($meta['tistory']['url'])) {
            $stats = $this->fetchTistoryStats($meta['tistory']);
            if ($stats) {
                $post->tistory_ctr = $this->calculateCtr($stats['clicks'], $stats['impressions']);
            }
        }

        $post->save();
    }

    protected function calculateCtr(int|float $clicks, int|float $impressions): ?float
    {
        if ($impressions <= 0) {
            return null;
        }
        return round(($clicks / $impressions) * 100, 2);
    }

    /**
     * TODO: 여기서 실제 워드프레스 통계 API 연동
     *  - Jetpack Stats API
     *  - 또는 별도의 GA / GSC 연동
     */
    protected function fetchWordPressStats(array $wpMeta): ?array
    {
        $postId = $wpMeta['post_id'] ?? null;
        $url    = $wpMeta['url'] ?? null;

        if (!$postId && !$url) {
            return null;
        }

        // 예시 뼈대: 실제 엔드포인트는 너 계정 기준으로 채우기
        // $response = $this->client->get('https://your-wp-stats-api', [...]);
        // $data = json_decode($response->getBody()->getContents(), true);

        // --- 임시 구조 예시 ---
        // return [
        //     'impressions' => $data['impressions'],
        //     'clicks'      => $data['clicks'],
        // ];

        return null; // 아직 API 없음 → null
    }

    /**
     * TODO: 티스토리 통계 API 연동
     */
    protected function fetchTistoryStats(array $tiMeta): ?array
    {
        $postId = $tiMeta['post_id'] ?? null;
        $url    = $tiMeta['url'] ?? null;

        if (!$postId && !$url) {
            return null;
        }

        // 예시 뼈대: 실제 엔드포인트는 네가 갖고 있는 도구(GA, GSC, 자체 로그 등)에 맞게 채우기
        // $response = $this->client->get('https://your-tistory-stats-api', [...]);
        // $data = json_decode($response->getBody()->getContents(), true);
        //
        // return [
        //     'impressions' => $data['impressions'],
        //     'clicks'      => $data['clicks'],
        // ];

        return null;
    }
}
