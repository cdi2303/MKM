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
    
    public function generateSmartThumbnail($prompt)
    {
        // 이미지 생성
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'size' => '1024x1024'
            ]);

        $url = $response->json()['data'][0]['url'] ?? null;
        if (!$url) return null;

        // 16:9 크롭 처리 (로컬 저장)
        $imageData = file_get_contents($url);
        $filename = 'thumbnails/' . time() . '.jpg';
        $fullPath = public_path($filename);

        // Intervention Image 사용 (composer 필요)
        // composer require intervention/image
        $img = \Image::make($imageData);

        // 16:9 비율 계산
        $targetW = 1280;
        $targetH = 720;
        $img->fit($targetW, $targetH, null, 'center');

        $img->save($fullPath, 85);

        return '/' . $filename;
    }

}
