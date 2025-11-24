<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('post_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('cron_expression');   // ex: "0 10 * * 1,3,5"
            $table->boolean('active')->default(true);

            // 옵션: 자동 생성/SEO 검사/태그 생성 여부
            $table->boolean('auto_generate')->default(true);
            $table->boolean('auto_analyze')->default(true);
            $table->boolean('auto_thumbnail')->default(true);
            $table->boolean('auto_publish')->default(true);

            $table->text('config')->nullable(); // reserved for future
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_schedules');
    }
};
