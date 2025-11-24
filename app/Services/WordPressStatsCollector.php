<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Post;

class WordPressStatsCollector
{
    protected ?string $username;
    protected ?string $appPassword;
    protected ?string $baseUrl;
    protected Client $client;

    public function __construct()
    {
        $this->baseUrl     = rtrim(env('WORDPRESS_BASE_URL', ''), '/');
        $this->username    = env('WORDPRESS_USERNAME', '');
        $this->appPassword = env('WORDPRESS_APP_PASSWORD', '');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 15,
        ]);
    }

    /**
     * WordPress에서 통계 수집
     */
    public function collect(Post $post): array
    {
        // WordPress가 아닌 경우 skip
        if ($post->platform !== 'wordpress') {
            return [false, 'Not WordPress'];
        }

        if (!$post->external_id) {
            return [false, 'Missing external_id'];
        }

        try {
            $response = $this->client->get(
                "/wp-json/wp/v2/posts/{$post->external_id}",
                ['auth' => [$this->username, $this->appPassword]]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            // 통계 예시 (실제 사이트 플러그인에 따라 다름)
            $views   = $data['meta']['views']   ?? 0;
            $clicks  = $data['meta']['clicks']  ?? 0;
            $ctr     = $views > 0 ? round(($clicks / $views) * 100, 2) : 0;

            return [true, compact('views', 'clicks', 'ctr')];

        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }
}
