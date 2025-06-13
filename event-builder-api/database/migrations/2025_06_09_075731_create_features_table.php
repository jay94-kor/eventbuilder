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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 요소 이름
            $table->string('icon'); // 아이콘 (파일명 또는 아이콘 클래스)
            $table->text('description')->nullable(); // 요소 설명
            $table->foreignId('category_id')->constrained('feature_categories')->onDelete('cascade'); // 카테고리 외래키
            $table->json('recommendations')->nullable(); // 연관 추천 요소들 (JSON 배열)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
