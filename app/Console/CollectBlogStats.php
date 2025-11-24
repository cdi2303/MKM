<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BlogStatsService;

class CollectBlogStats extends Command
{
    protected $signature = 'stats:collect';
    protected $description = 'Collect WordPress / Tistory stats and calculate CTR for posts';

    public function __construct(protected BlogStatsService $statsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Collecting blog stats...');

        $this->statsService->collectAll();

        $this->info('Done.');

        return self::SUCCESS;
    }
}
