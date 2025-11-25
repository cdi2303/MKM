<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LocalLLMService
{
    protected $url;
    protected $model;

    public function __construct()
    {
        $this->url = config('services.localllm.url');
        $this->model = config('services.localllm.model');
    }

    public function chat($prompt)
    {
        $response = Http::post($this->url, [
            "model" => $this->model,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 1024
        ]);

        return $response->json()['choices'][0]['message']['content'] ?? '';
    }
}
