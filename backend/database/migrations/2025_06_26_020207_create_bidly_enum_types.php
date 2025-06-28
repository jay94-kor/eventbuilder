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
        // 기존 ENUM 타입이 존재할 경우 먼저 드롭
        // (RFP_ELEMENT_TYPE_ENUM, NOTIFICATION_TYPE_ENUM, SCHEDULE_ACTIVITY_TYPE_ENUM은 VARCHAR로 사용)
        DB::statement("DROP TYPE IF EXISTS SCHEDULE_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS EVALUATOR_ASSIGNMENT_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS VENDOR_ACCOUNT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS SUBSCRIPTION_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS PAYMENT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS PROPOSAL_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS ANNOUNCEMENT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS ANNOUNCEMENT_CHANNEL_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS APPROVAL_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS RFP_ISSUE_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS RFP_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS USER_TYPE_ENUM");

        // 사용자 타입
        DB::statement("CREATE TYPE USER_TYPE_ENUM AS ENUM ('agency_member', 'vendor_member', 'admin')");
        DB::statement("COMMENT ON TYPE USER_TYPE_ENUM IS '사용자 타입: 대행사 직원, 용역사 직원, 관리자'");

        // RFP 상태
        DB::statement("CREATE TYPE RFP_STATUS_ENUM AS ENUM ('draft', 'approval_pending', 'approved', 'rejected', 'published', 'closed')");
        DB::statement("COMMENT ON TYPE RFP_STATUS_ENUM IS 'RFP 상태: 초안, 결재 대기, 승인됨, 반려됨, 공고 중, 마감됨'");

        // RFP 발주 타입
        DB::statement("CREATE TYPE RFP_ISSUE_TYPE_ENUM AS ENUM ('integrated', 'separated_by_element', 'separated_by_group')");
        DB::statement("COMMENT ON TYPE RFP_ISSUE_TYPE_ENUM IS 'RFP 발주 타입: 통합 발주, 요소별 분리 발주, 부분 묶음 발주'");

        // RFP 요소 타입은 VARCHAR로 관리 (관리자가 동적으로 추가/삭제 가능)
        // element_definitions 테이블에서 관리됨

        // 결재 상태
        DB::statement("CREATE TYPE APPROVAL_STATUS_ENUM AS ENUM ('pending', 'approved', 'rejected')");
        DB::statement("COMMENT ON TYPE APPROVAL_STATUS_ENUM IS '결재 상태: 대기 중, 승인됨, 반려됨'");

        // 공고 채널 타입
        DB::statement("CREATE TYPE ANNOUNCEMENT_CHANNEL_TYPE_ENUM AS ENUM ('agency_private', 'public')");
        DB::statement("COMMENT ON TYPE ANNOUNCEMENT_CHANNEL_TYPE_ENUM IS '공고 채널 타입: 대행사 전용 채널, 공용 채널'");

        // 공고 상태
        DB::statement("CREATE TYPE ANNOUNCEMENT_STATUS_ENUM AS ENUM ('open', 'closed', 'awarded')");
        DB::statement("COMMENT ON TYPE ANNOUNCEMENT_STATUS_ENUM IS '공고 상태: 열림, 닫힘, 낙찰됨'");

        // 제안서 상태
        DB::statement("CREATE TYPE PROPOSAL_STATUS_ENUM AS ENUM ('submitted', 'under_review', 'awarded', 'rejected')");
        DB::statement("COMMENT ON TYPE PROPOSAL_STATUS_ENUM IS '제안서 상태: 제출됨, 검토 중, 낙찰됨, 거절됨'");

        // 계약 대금 지급 상태
        DB::statement("CREATE TYPE PAYMENT_STATUS_ENUM AS ENUM ('pending', 'prepayment_paid', 'balance_paid', 'all_paid')");
        DB::statement("COMMENT ON TYPE PAYMENT_STATUS_ENUM IS '계약 대금 지급 상태: 대기 중, 선금 지급됨, 잔금 지급됨, 모두 지급됨'");

        // 알림 타입은 VARCHAR로 관리 (관리자가 동적으로 추가/삭제 가능)
        // 기본값: new_bid_available, proposal_submitted, rfp_approved, contract_status_update, rfp_rejected, announcement_closed

        // 대행사 구독 상태
        DB::statement("CREATE TYPE SUBSCRIPTION_STATUS_ENUM AS ENUM ('active', 'inactive', 'trial_expired', 'payment_pending')");
        DB::statement("COMMENT ON TYPE SUBSCRIPTION_STATUS_ENUM IS '대행사 구독 상태: 활성, 비활성, 체험 기간 만료, 결제 대기 중'");

        // 용역사 계정 상태
        DB::statement("CREATE TYPE VENDOR_ACCOUNT_STATUS_ENUM AS ENUM ('active', 'suspended', 'permanently_banned')");
        DB::statement("COMMENT ON TYPE VENDOR_ACCOUNT_STATUS_ENUM IS '용역사 계정 상태: 활성, 일시 정지됨, 영구 제명됨'");

        // 심사위원 배정 방식
        DB::statement("CREATE TYPE EVALUATOR_ASSIGNMENT_TYPE_ENUM AS ENUM ('random', 'designated')");
        DB::statement("COMMENT ON TYPE EVALUATOR_ASSIGNMENT_TYPE_ENUM IS '심사위원 배정 방식: 무작위 배정, 지정 배정'");

        // 스케줄 상태
        DB::statement("CREATE TYPE SCHEDULE_STATUS_ENUM AS ENUM ('planned', 'ongoing', 'completed', 'cancelled')");
        DB::statement("COMMENT ON TYPE SCHEDULE_STATUS_ENUM IS '스케줄 상태: 계획됨, 진행 중, 완료됨, 취소됨'");

        // 스케줄 활동 유형은 VARCHAR로 관리 (관리자가 동적으로 추가/삭제 가능)
        // 기본값: meeting, delivery, installation, dismantling, rehearsal, event_execution, setup, testing, 
        // load_in, load_out, storage, breakdown, cleaning, training, briefing, pickup, transportation, 
        // site_visit, concept_meeting, technical_rehearsal, dress_rehearsal, final_inspection, wrap_up
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down()에서는 실제로 생성된 ENUM들만 삭제
        DB::statement("DROP TYPE IF EXISTS SCHEDULE_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS EVALUATOR_ASSIGNMENT_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS VENDOR_ACCOUNT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS SUBSCRIPTION_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS PAYMENT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS PROPOSAL_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS ANNOUNCEMENT_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS ANNOUNCEMENT_CHANNEL_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS APPROVAL_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS RFP_ISSUE_TYPE_ENUM");
        DB::statement("DROP TYPE IF EXISTS RFP_STATUS_ENUM");
        DB::statement("DROP TYPE IF EXISTS USER_TYPE_ENUM");
    }
};
