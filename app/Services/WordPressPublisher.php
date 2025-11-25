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

        // WordPress 응답에서 URL 추출
        return [true, [
            'id'   => $data['id'] ?? null,
            'slug' => $data['slug'] ?? null,
            'url'  => $data['link'] ?? null,  // ← 최종 URL
        ]];

    } catch (RequestException $e) {
        $msg = $e->getMessage();
        if ($e->hasResponse()) {
            $msg .= ' | ' . $e->getResponse()->getBody()->getContents();
        }
        return [false, $msg];
    }
}
