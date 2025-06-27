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
        // SCHEDULE_ACTIVITY_TYPE_ENUM 생성
        DB::statement("
            CREATE TYPE SCHEDULE_ACTIVITY_TYPE_ENUM AS ENUM (
                'meeting',
                'delivery', 
                'installation',
                'dismantling',
                'rehearsal',
                'event_execution',
                'setup',
                'testing', 
                'load_in',
                'load_out',
                'storage',
                'breakdown',
                'cleaning',
                'training',
                'briefing',
                'pickup',
                'transportation',
                'site_visit',
                'concept_meeting',
                'technical_rehearsal',
                'dress_rehearsal',
                'final_inspection',
                'wrap_up'
            )
        ");

        Schema::table('schedules', function (Blueprint $table) {
            $table->string('type')->nullable()->after('status')->comment('스케줄 활동 유형');
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

        DB::statement("DROP TYPE IF EXISTS SCHEDULE_ACTIVITY_TYPE_ENUM");
    }
};
