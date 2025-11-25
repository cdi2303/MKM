<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodexMaxService
{
    protected string $url;
    protected string $model;

    public function __construct()
    {
        // ✅ 여기서 절대 엔드포인트를 또 붙이면 안됨
        $this->url   = config('services.codexmax.url');
        $this->model = config('services.codexmax.model', 'local');

        if (!$this->url) {
            throw new \Exception("CodexMax URL is not configured ❌");
        }
    }

    public function chat(string $prompt): string
    {
        try {
            $res = Http::timeout(120)->post($this->url, [
                "model"      => $this->model,
                "messages"   => [[ "role" => "user", "content" => $prompt ]],
                "max_tokens" => 512
            ]);

            if ($res->failed()) {
                Log::error("CodexMax HTTP FAILED", ['body'=>$res->body()]);
                return '';
            }

            return $res->json()['choices'][0]['message']['content'] ?? '';
        } catch (\Throwable $e) {
            Log::error("CodexMax EXCEPTION", ['msg'=>$e->getMessage()]);
            return '';
        }
    }
}
