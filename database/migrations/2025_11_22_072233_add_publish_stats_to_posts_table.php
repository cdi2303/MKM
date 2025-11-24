<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 워드프레스 / 티스토리 발행 정보
            $table->json('publish_meta')->nullable()->after('meta'); // 예: {"wordpress": {...}, "tistory": {...}}

            // 단순화된 CTR 저장용 (원하면 JSON만 써도 됨)
            $table->float('wp_ctr')->nullable()->after('publish_meta');
            $table->float('tistory_ctr')->nullable()->after('wp_ctr');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['publish_meta', 'wp_ctr', 'tistory_ctr']);
        });
    }
};
