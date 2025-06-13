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
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->integer('sort_order')->default(0)->after('description');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        // 기존 데이터에 대해 slug 생성
        \DB::table('feature_categories')->get()->each(function ($category) {
            \DB::table('feature_categories')
                ->where('id', $category->id)
                ->update([
                    'slug' => \Illuminate\Support\Str::slug($category->name . '-' . $category->id)
                ]);
        });

        // 이제 slug를 NOT NULL로 변경
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'sort_order', 'is_active']);
        });
    }
};
