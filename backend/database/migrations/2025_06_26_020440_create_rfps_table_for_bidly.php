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
        Schema::create('rfps', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('project_id')->comment('연결된 프로젝트 ID (FK)');
            $table->string('current_status')->comment('RFP 현재 상태 (RFP_STATUS_ENUM)'); // STRING으로 선언
            $table->uuid('created_by_user_id')->comment('RFP를 생성한 사용자 ID (FK)');
            $table->uuid('agency_id')->comment('해당 RFP를 생성한 대행사 ID (FK)');
            $table->string('issue_type')->comment('RFP 발주 타입 (RFP_ISSUE_TYPE_ENUM)'); // STRING으로 선언
            $table->text('rfp_description')->nullable()->comment('최종 생성된 RFP의 요약된 설명');
            $table->dateTime('closing_at')->comment('공고 마감 일시');
            $table->dateTime('published_at')->nullable()->comment('공고 게시 일시');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        // ENUM 타입으로 컬럼 변경 및 DEFAULT 값 설정
        DB::statement("ALTER TABLE rfps ALTER COLUMN current_status TYPE RFP_STATUS_ENUM USING current_status::RFP_STATUS_ENUM");
        DB::statement("ALTER TABLE rfps ALTER COLUMN current_status SET DEFAULT 'draft'"); // <--- 추가된 줄
        DB::statement("ALTER TABLE rfps ALTER COLUMN issue_type TYPE RFP_ISSUE_TYPE_ENUM USING issue_type::RFP_ISSUE_TYPE_ENUM");
        DB::statement("COMMENT ON TABLE rfps IS '제안요청서(RFP)의 메타 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfps');
    }
};
