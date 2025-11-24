<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {

            if (!Schema::hasColumn('posts', 'views')) {
                $table->integer('views')->default(0);
            }

            if (!Schema::hasColumn('posts', 'clicks')) {
                $table->integer('clicks')->default(0);
            }

            if (!Schema::hasColumn('posts', 'ctr')) {
                $table->decimal('ctr', 5, 2)->default(0);
            }

            if (!Schema::hasColumn('posts', 'likes')) {
                $table->integer('likes')->default(0);
            }

            if (!Schema::hasColumn('posts', 'comments')) {
                $table->integer('comments')->default(0);
            }

            if (!Schema::hasColumn('posts', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {

            // 삭제도 안전하게
            if (Schema::hasColumn('posts', 'views')) {
                $table->dropColumn('views');
            }
            if (Schema::hasColumn('posts', 'clicks')) {
                $table->dropColumn('clicks');
            }
            if (Schema::hasColumn('posts', 'ctr')) {
                $table->dropColumn('ctr');
            }
            if (Schema::hasColumn('posts', 'likes')) {
                $table->dropColumn('likes');
            }
            if (Schema::hasColumn('posts', 'comments')) {
                $table->dropColumn('comments');
            }
            if (Schema::hasColumn('posts', 'last_synced_at')) {
                $table->dropColumn('last_synced_at');
            }
        });
    }
};
