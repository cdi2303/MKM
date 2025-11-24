<?php

namespace App\Services;

use App\Models\Post;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WordPressPublisher
{
    protected Client $client;
    protected string $baseUrl;
    protected string $username;
    protected string $appPassword;

    public function __construct()
    {
        $this->baseUrl     = rtrim(env('WORDPRESS_BASE_URL'), '/') . '/';
        $this->username    = env('WORDPRESS_USERNAME');
        $this->appPassword = env('WORDPRESS_APP_PASSWORD');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 15,
        ]);
    }

    public function publish(Post $post): array
    {
        try {
            $response = $this->client->post('wp-json/wp/v2/posts', [
                'auth' => [$this->username, $this->appPassword],
                'json' => [
                    'title'   => $post->title,
                    'content' => $post->html,
                    'status'  => 'publish',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                true,
                [
                    'id'   => $data['id'] ?? null,
                    'slug' => $data['slug'] ?? null,
                    'url'  => $data['link'] ?? null   // ⭐ 최종 URL
                ]
            ];
        } catch (RequestException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $msg .= ' | ' . $e->getResponse()->getBody()->getContents();
            }
            return [false, $msg];
        }
    }
}
