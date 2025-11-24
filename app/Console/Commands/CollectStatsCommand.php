<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use App\Services\WordPressStatsCollector;
use App\Services\TistoryStatsCollector;
use App\Services\CtrCalculator;

class CollectStatsCommand extends Command
{
    protected $signature = 'stats:collect';
    protected $description = 'Collect WordPress / Tistory stats for all posts';

    public function handle()
    {
        $this->info("📊 시작: WordPress / Tistory 통계 수집");

        $wp     = new WordPressStatsCollector();
        $ti     = new TistoryStatsCollector();
        $ctr    = new CtrCalculator();

        $posts = Post::whereNotNull('external_post_id')->get();

        foreach ($posts as $post) {

            $this->line("➡ {$post->title}");

            if ($post->published_to === 'wordpress') {
                $stats = $wp->fetchStats($post);
            } else {
                $stats = $ti->fetchStats($post);
            }

            if (isset($stats['error'])) {
                $this->error("   ❌ 오류: {$stats['error']}");
                continue;
            }

            // CTR 계산
            $post->meta = array_merge($post->meta ?? [], [
                'stats' => $stats,
                'ctr'   => $ctr->calculate(
                    $stats['views'] ?? null,
                    $stats['impressions'] ?? null
                )
            ]);

            $post->save();

            $this->info("   ✅ 저장 완료");
        }

        $this->info("📁 작업 완료!");
        return Command::SUCCESS;
    }
}
