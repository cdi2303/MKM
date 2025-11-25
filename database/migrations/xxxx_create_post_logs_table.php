<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('post_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('action');            // generate, analyze, save, publish, stats
            $table->json('request')->nullable(); // 요청 데이터
            $table->json('response')->nullable(); // 응답 데이터
            $table->boolean('success')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down() {
        Schema::dropIfExists('post_logs');
    }
};
