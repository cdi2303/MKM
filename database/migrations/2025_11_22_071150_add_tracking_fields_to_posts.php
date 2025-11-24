<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('views')->nullable()->default(null);
            $table->integer('clicks')->nullable()->default(null);
            $table->float('ctr', 5, 2)->nullable()->default(null);

            $table->integer('likes')->nullable()->default(null);
            $table->integer('comments')->nullable()->default(null);

            $table->timestamp('last_synced_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //
        });
    }
};
