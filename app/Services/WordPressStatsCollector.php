<?php

namespace App\Services;

use App\Models\Post;
use GuzzleHttp\Client;

class WordPressStatsCollector
{
    protected Client $client;
    protected string $baseUrl;
    protected string $username;
    protected string $appPassword;

    public function __construct()
    {
        $this->baseUrl     = rtrim(env('WORDPRESS_BASE_URL'), '/') . '/';
        $this->username    = env('WORDPRESS_USERNAME') ?? '';
        $this->appPassword = env('WORDPRESS_APP_PASSWORD') ?? '';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10,
        ]);
    }

    /**
     * WordPress 조회수 수집
     */
    public function collect(Post $post): array
    {
        try {
            if (!$post->external_id) {
                return [false, "external_id 없음"];
            }

            $url = "wp-json/wp/v2/posts/{$post->external_id}";

            $response = $this->client->get($url, [
                'auth' => [$this->username, $this->appPassword]
            ]);

            $data = json_decode($response->getBody(), true);

            // WP 통계는 플러그인을 반드시 사용해야 조회 가능
            $views = $data['meta']['views'] ?? 0;

            return [true, [
                'views'  => $views,
                'clicks' => 0,
                'ctr'    => 0,
            ]];

        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }
}
