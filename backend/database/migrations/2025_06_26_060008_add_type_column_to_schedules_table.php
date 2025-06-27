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
        // ENUM 타입 적용 (ENUM은 이미 첫 번째 마이그레이션에서 생성됨)
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('type')->nullable()->after('status')->comment('스케줄 활동 유형');
        });

        // ENUM 타입으로 변경
        DB::statement("ALTER TABLE schedules ALTER COLUMN type TYPE SCHEDULE_ACTIVITY_TYPE_ENUM USING type::SCHEDULE_ACTIVITY_TYPE_ENUM");
        
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
