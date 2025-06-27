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
        Schema::create('announcement_evaluators', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('announcement_id')->comment('공고 ID (FK)');
            $table->uuid('user_id')->comment('심사위원 사용자 ID (FK)');
            $table->string('assignment_type')->comment('배정 방식 (EVALUATOR_ASSIGNMENT_TYPE_ENUM)'); // STRING으로 선언
            $table->timestamp('assigned_at')->useCurrent()->comment('심사위원 배정 일시');
            $table->timestamps();

            // 외래 키 제약조건
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // 한 공고에 같은 심사위원은 한 번만 배정 가능
            $table->unique(['announcement_id', 'user_id'])->comment('한 공고에 같은 심사위원은 한 번만 배정 가능');
        });

        // ENUM 타입 적용 (ENUM은 이미 첫 번째 마이그레이션에서 생성됨)
        DB::statement("ALTER TABLE announcement_evaluators ALTER COLUMN assignment_type TYPE EVALUATOR_ASSIGNMENT_TYPE_ENUM USING assignment_type::EVALUATOR_ASSIGNMENT_TYPE_ENUM");
        DB::statement("ALTER TABLE announcement_evaluators ALTER COLUMN assignment_type SET DEFAULT 'designated'");
        DB::statement("COMMENT ON TABLE announcement_evaluators IS '공고에 배정된 심사위원 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_evaluators');
        // ENUM 타입은 첫 번째 마이그레이션에서 관리하므로 여기서는 삭제하지 않음
    }
};
