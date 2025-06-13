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
        Schema::create('feature_recommendation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->foreignId('recommended_feature_id')->constrained('features')->onDelete('cascade');
            $table->timestamps();
            
            // 같은 조합의 중복 방지
            $table->unique(['feature_id', 'recommended_feature_id']);
            
            // 인덱스 추가
            $table->index(['feature_id']);
            $table->index(['recommended_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_recommendation');
    }
};
