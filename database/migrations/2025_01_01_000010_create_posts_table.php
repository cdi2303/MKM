<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('posts', function(Blueprint $table){
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('keyword')->nullable();
            $table->text('title')->nullable();
            $table->longText('content')->nullable();
            $table->longText('html')->nullable();
            $table->json('meta')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('posts');
    }
};
