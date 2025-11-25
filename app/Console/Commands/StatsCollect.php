<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Services\WordPressStatsCollector;

class StatsCollect extends Command
{
    protected $signature = 'stats:collect';
    protected $description = 'Collect stats from WordPress';

    public function handle()
    {
        $this->info("📊 시작: WordPress 통계 수집");

        $wp = new WordPressStatsCollector();

        // 플랫폼이 'wordpress'인 포스트만
        $posts = Post::where('platform', 'wordpress')->get();

        foreach ($posts as $post) {

            [$ok, $data] = $wp->collect($post);

            if (!$ok) {
                $this->warn("⚠️ 실패: Post #{$post->id} → {$data}");
                continue;
            }

            $post->update([
                'views'          => $data['views'] ?? 0,
                'clicks'         => $data['clicks'] ?? 0,
                'ctr'            => $data['ctr'] ?? 0,
                'likes'          => $data['likes'] ?? 0,
                'comments'       => $data['comments'] ?? 0,
                'last_synced_at' => now(),
            ]);

            $this->info("✔️ 성공: Post #{$post->id}");
        }

        $this->info("🎉 완료");
    }
}
