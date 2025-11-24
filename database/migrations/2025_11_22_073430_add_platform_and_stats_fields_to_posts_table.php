<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {

            // 플랫폼 정보
            if (!Schema::hasColumn('posts', 'platform')) {
                $table->string('platform')->nullable()->after('meta');
            }

            if (!Schema::hasColumn('posts', 'external_id')) {
                $table->string('external_id')->nullable()->after('platform');
            }

            if (!Schema::hasColumn('posts', 'external_slug')) {
                $table->string('external_slug')->nullable()->after('external_id');
            }

            if (!Schema::hasColumn('posts', 'wp_api_url')) {
                $table->string('wp_api_url')->nullable()->after('external_slug');
            }

            if (!Schema::hasColumn('posts', 'tistory_access_token')) {
                $table->string('tistory_access_token')->nullable()->after('wp_api_url');
            }

            if (!Schema::hasColumn('posts', 'blog_name')) {
                $table->string('blog_name')->nullable()->after('tistory_access_token');
            }

            // 통계 필드
            if (!Schema::hasColumn('posts', 'views')) {
                $table->integer('views')->default(0)->after('blog_name');
            }

            if (!Schema::hasColumn('posts', 'clicks')) {
                $table->integer('clicks')->default(0)->after('views');
            }

            if (!Schema::hasColumn('posts', 'ctr')) {
                $table->float('ctr')->default(0)->after('clicks');
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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'platform',
                'external_id',
                'external_slug',
                'wp_api_url',
                'tistory_access_token',
                'blog_name',
                'views',
                'clicks',
                'ctr',
                'likes',
                'comments',
                'last_synced_at',
            ]);
        });
    }

};
