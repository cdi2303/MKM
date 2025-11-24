<?php

namespace App\Services;

use App\Models\Post;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TistoryStatsCollector
{
    protected Client $client;
    protected string $accessToken;
    protected string $blogName;

    public function __construct()
    {
        $this->accessToken = env('TISTORY_ACCESS_TOKEN');
        $this->blogName    = env('TISTORY_BLOG_NAME');

        $this->client = new Client([
            'timeout' => 10,
        ]);
    }

    public function collect(Post $post): array
    {
        try {
            $response = $this->client->get("https://www.tistory.com/apis/post/read", [
                'query' => [
                    'access_token' => $this->accessToken,
                    'output'       => 'json',
                    'blogName'     => $this->blogName,
                    'postId'       => $post->external_id,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $info = $data['tistory']['item'] ?? [];

            return [
                'views'     => $info['post']['hits'] ?? 0,
                'clicks'    => 0,
                'ctr'       => 0,
                'likes'     => $info['post']['likes'] ?? 0,
                'comments'  => $info['comments'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error("Tistory Stats Error: " . $e->getMessage());
            return [];
        }
    }
}
