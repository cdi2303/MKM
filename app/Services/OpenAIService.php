<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = "https://api.openai.com/v1/chat/completions";

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * 기본 text 생성 (JSON, 설명 등)
     */
    public function generate(array $params)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl, array_merge([
                'model' => 'gpt-4o-mini',
            ], $params));

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    /**
     * HTML 생성 전용 (본문 생성 등)
     */
    public function generateHTML(array $params)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl, array_merge([
                'model' => 'gpt-4o-mini',
            ], $params));

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }

    /**
     * 이미지 생성
     */
    public function generateImage($prompt)
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'size' => '512x512'
            ]);

        return $response->json()['data'][0]['url'] ?? null;
    }
}
