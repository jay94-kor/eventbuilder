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
        Schema::create('rfp_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('rfp_id')->comment('결재 대상 RFP ID (FK)');
            $table->uuid('approver_user_id')->comment('결재자 사용자 ID (FK)');
            $table->string('status')->comment('결재 상태 (APPROVAL_STATUS_ENUM)'); // STRING으로 선언
            $table->text('comment')->nullable()->comment('결재 시 남긴 코멘트 (예: 반려 사유)');
            $table->timestamp('approved_at')->nullable()->comment('결재 완료 일시');
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
            $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('restrict');
        });
        // ENUM 타입으로 컬럼 변경 및 DEFAULT 값 설정
        DB::statement("ALTER TABLE rfp_approvals ALTER COLUMN status TYPE APPROVAL_STATUS_ENUM USING status::APPROVAL_STATUS_ENUM");
        DB::statement("ALTER TABLE rfp_approvals ALTER COLUMN status SET DEFAULT 'pending'"); // <--- 추가된 줄
        DB::statement("COMMENT ON TABLE rfp_approvals IS 'RFP에 대한 결재 이력을 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfp_approvals');
    }
};
