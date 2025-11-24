<?php

namespace App\Services;

use App\Models\Post;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TistoryPublisher
{
    protected Client $client;
    protected string $accessToken;
    protected string $blogName;

    public function __construct()
    {
        $this->accessToken = env('TISTORY_ACCESS_TOKEN');
        $this->blogName    = env('TISTORY_BLOG_NAME');

        $this->client = new Client([
            'timeout' => 15,
        ]);
    }

    public function publish(Post $post): array
    {
        try {
            $response = $this->client->post('https://www.tistory.com/apis/post/write', [
                'form_params' => [
                    'access_token' => $this->accessToken,
                    'output'       => 'json',
                    'blogName'     => $this->blogName,
                    'title'        => $post->title,
                    'content'      => $post->html,
                    'visibility'   => 3,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                true,
                [
                    'id'  => $data['tistory']['item']['postId'] ?? null,
                    'url' => $data['tistory']['item']['url'] ?? null   // ⭐ 최종 URL
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
