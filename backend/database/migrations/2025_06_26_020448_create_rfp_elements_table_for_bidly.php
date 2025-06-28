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
        Schema::create('rfp_elements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('rfp_id')->comment('연결된 RFP ID (FK)');
            $table->string('element_type', 100)->comment('요소 타입 (VARCHAR 100)'); // VARCHAR로 선언
            $table->jsonb('details')->nullable()->comment('각 요소별 상세 스펙 (JSONB)');
            $table->decimal('allocated_budget', 15, 2)->nullable()->comment('이 요소에 배정된 예산');
            $table->decimal('prepayment_ratio', 5, 2)->nullable()->comment('선금 비율 (0~1 사이 값)');
            $table->timestamp('prepayment_due_date')->nullable()->comment('선금 지급 예정일');
            $table->decimal('balance_ratio', 5, 2)->nullable()->comment('잔금 비율 (0~1 사이 값)');
            $table->timestamp('balance_due_date')->nullable()->comment('잔금 지급 예정일');
            $table->uuid('parent_rfp_element_id')->nullable()->comment('부분 묶음 발주 시, 이 요소가 속한 그룹의 대표 요소 ID'); // Self-referencing FK
            $table->timestamps();

            $table->foreign('rfp_id')->references('id')->on('rfps')->onDelete('cascade');
        });

        // Self-referencing FK는 테이블 생성 후 별도로 추가
        Schema::table('rfp_elements', function (Blueprint $table) {
            $table->foreign('parent_rfp_element_id')->references('id')->on('rfp_elements')->onDelete('set null');
        });

        // VARCHAR로 유지 (ENUM 사용하지 않음)
        DB::statement("COMMENT ON TABLE rfp_elements IS '각 RFP에 포함된 요소(무대, 음향 등)의 상세 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfp_elements');
    }
};
