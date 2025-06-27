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
        Schema::create('schedule_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('schedule_id')->comment('스케줄 ID (FK)');
            $table->uuid('user_id')->comment('업로드한 사용자 ID (FK)');
            $table->string('file_path')->comment('파일 저장 경로');
            $table->string('file_name')->comment('원본 파일명');
            $table->string('file_type')->comment('파일 MIME 타입');
            $table->bigInteger('file_size')->comment('파일 크기 (bytes)');
            $table->timestamps();

            // 외래 키 제약조건
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // 인덱스
            $table->index('schedule_id');
            $table->index('user_id');
        });

        DB::statement("COMMENT ON TABLE schedule_attachments IS '스케줄에 첨부된 파일 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_attachments');
    }
};
