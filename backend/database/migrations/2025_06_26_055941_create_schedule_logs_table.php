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
        Schema::create('schedule_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('schedule_id')->comment('이력이 기록된 스케줄 ID (FK)');
            $table->uuid('user_id')->nullable()->comment('변경을 수행한 사용자 ID (FK)'); // 누가 변경했는지
            $table->string('action')->comment('수행된 작업 (예: created, updated, deleted, status_changed)');
            $table->jsonb('old_values')->nullable()->comment('변경 전 값들 (JSONB)');
            $table->jsonb('new_values')->nullable()->comment('변경 후 값들 (JSONB)');
            $table->timestamps(); // created_at, updated_at

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
        
        DB::statement("COMMENT ON TABLE schedule_logs IS '스케줄 변경 이력을 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_logs');
    }
};
