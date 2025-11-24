<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {

            if (!Schema::hasColumn('posts', 'platform')) {
                $table->string('platform')->nullable()->after('meta');
            }
            if (!Schema::hasColumn('posts', 'external_id')) {
                $table->string('external_id')->nullable()->after('platform');
            }
            if (!Schema::hasColumn('posts', 'external_slug')) {
                $table->string('external_slug')->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('posts', 'external_url')) {
                $table->string('external_url')->nullable()->after('external_slug');
            }

            if (!Schema::hasColumn('posts', 'views')) {
                $table->integer('views')->default(0)->after('external_url');
            }
            if (!Schema::hasColumn('posts', 'clicks')) {
                $table->integer('clicks')->default(0)->after('views');
            }
            if (!Schema::hasColumn('posts', 'ctr')) {
                $table->decimal('ctr', 5, 2)->default(0)->after('clicks');
            }
            if (!Schema::hasColumn('posts', 'likes')) {
                $table->integer('likes')->default(0)->after('ctr');
            }
            if (!Schema::hasColumn('posts', 'comments')) {
                $table->integer('comments')->default(0)->after('likes');
            }
            if (!Schema::hasColumn('posts', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('comments');
            }
        });
    }

    public function down()
    {
        // 필요시 제거
    }
};
