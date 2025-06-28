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
        // VARCHAR 타입 적용
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('type', 100)->nullable()->after('status')->comment('스케줄 활동 유형 (VARCHAR 100)');
        });
        
        // 기존 데이터에 대해 기본값 설정 (선택사항)
        DB::statement("UPDATE schedules SET type = 'meeting' WHERE type IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // ENUM 타입은 첫 번째 마이그레이션에서 관리하므로 여기서는 삭제하지 않음
    }
};
