<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            // evaluation_criteria 컬럼 추가: JSONB 타입, Null 허용, 기본값 빈 객체
            $table->jsonb('evaluation_criteria')->default('{}')->comment('공고 평가 기준 (점수 비중, 가격 점수 규칙 등)');
        });

        // 컬럼에 대한 주석 추가 (선택 사항이지만 권장)
        DB::statement("COMMENT ON COLUMN announcements.evaluation_criteria IS '공고 평가 기준 (점수 비중, 가격 점수 규칙 등)'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('evaluation_criteria');
        });
    }
};
