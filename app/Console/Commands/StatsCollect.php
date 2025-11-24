<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;
use App\Services\WordPressStatsCollector;
use App\Services\TistoryStatsCollector;

class StatsCollect extends Command
{
    protected $signature = 'stats:collect';
    protected $description = 'Collect stats from WordPress / Tistory';

    public function handle()
    {
        $this->info("📊 시작: WordPress / Tistory 통계 수집");

        $wp   = new WordPressStatsCollector();
        $tis  = new TistoryStatsCollector();

        // 플랫폼이 지정된 포스트만 수집
        $posts = Post::whereNotNull('platform')->get();

        foreach ($posts as $post) {

            if ($post->platform === 'wordpress') {
                [$ok, $data] = $wp->collect($post);
            } else {
                [$ok, $data] = $tis->collect($post);
            }

            if (!$ok) {
                $this->warn("⚠️ 실패: Post #{$post->id} → {$data}");
                continue;
            }

            // DB 업데이트
            $post->views = $data['views'] ?? 0;
            $post->clicks = $data['clicks'] ?? 0;
            $post->ctr = $data['ctr'] ?? 0;
            $post->last_synced_at = now();
            $post->save();

            $this->info("✔️ 성공: Post #{$post->id}");
        }

        $this->info("🎉 완료");
    }
}
