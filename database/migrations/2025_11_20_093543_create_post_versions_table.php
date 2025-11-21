<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('post_versions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('post_id');       // 원본 글 ID
        $table->unsignedInteger('version');          // 버전 번호
        $table->string('title')->nullable();         // 제목
        $table->string('keyword')->nullable();       // 키워드
        $table->longText('html')->nullable();        // HTML
        $table->longText('content')->nullable();     // 텍스트
        $table->json('meta')->nullable();            // 메타 정보
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_versions');
    }
};
