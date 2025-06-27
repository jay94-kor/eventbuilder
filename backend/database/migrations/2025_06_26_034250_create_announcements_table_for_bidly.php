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
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('rfp_id')->comment('연결된 RFP ID (FK)'); // FK to rfps
            $table->uuid('rfp_element_id')->nullable()->comment('특정 RFP 요소/그룹 ID (분리 발주 시) (FK)'); // FK to rfp_elements
            $table->uuid('agency_id')->comment('공고를 올린 대행사 ID (FK)'); // FK to agencies
            $table->string('title')->comment('공고 제목');
            $table->text('description')->nullable()->comment('공고 상세 설명');
            $table->decimal('estimated_price', 15, 2)->nullable()->comment('공고에 제시된 예상 금액');
            $table->dateTime('closing_at')->comment('제안서 제출 마감 일시');
            $table->string('channel_type')->comment('공고 채널 타입 (ANNOUNCEMENT_CHANNEL_TYPE_ENUM)'); // STRING으로 선언
            $table->boolean('contact_info_private')->default(false)->comment('연락처 비공개 여부');
            $table->timestamp('published_at')->nullable()->comment('공고 게시 일시');
            $table->string('status')->comment('공고 상태 (ANNOUNCEMENT_STATUS_ENUM)'); // STRING으로 선언
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
            $table->foreign('rfp_element_id')->references('id')->on('rfp_elements')->onDelete('set null');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        // ENUM 타입으로 컬럼 변경
        DB::statement("ALTER TABLE announcements ALTER COLUMN channel_type TYPE ANNOUNCEMENT_CHANNEL_TYPE_ENUM USING channel_type::ANNOUNCEMENT_CHANNEL_TYPE_ENUM");
        DB::statement("ALTER TABLE announcements ALTER COLUMN status TYPE ANNOUNCEMENT_STATUS_ENUM USING status::ANNOUNCEMENT_STATUS_ENUM");
        DB::statement("ALTER TABLE announcements ALTER COLUMN status SET DEFAULT 'open'"); // <--- 추가된 줄
        DB::statement("COMMENT ON TABLE announcements IS '용역사에게 공개되는 입찰 공고 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
