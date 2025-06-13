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
        Schema::table('features', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('category_id');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->boolean('is_premium')->default(false)->after('is_active');
            $table->json('config')->nullable()->after('is_premium');
        });

        // 기능 추천을 위한 pivot 테이블 생성
        Schema::create('feature_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_id')->constrained()->onDelete('cascade');
            $table->foreignId('recommended_feature_id')->constrained('features')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['feature_id', 'recommended_feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_recommendations');
        
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_active', 'is_premium', 'config']);
        });
    }
};
