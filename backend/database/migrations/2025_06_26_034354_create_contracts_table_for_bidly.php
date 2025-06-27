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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('announcement_id')->comment('연결된 공고 ID (FK)');
            $table->uuid('proposal_id')->unique()->comment('낙찰된 단 하나의 제안서 ID (FK)');
            $table->uuid('vendor_id')->comment('계약된 용역사 ID (FK)');
            $table->decimal('final_price', 15, 2)->comment('최종 계약 금액');
            $table->string('contract_file_path')->nullable()->comment('계약서 파일 경로');
            $table->timestamp('contract_signed_at')->nullable()->comment('계약 체결 일시');
            $table->decimal('prepayment_amount', 15, 2)->nullable()->comment('실제 선금 지급액');
            $table->timestamp('prepayment_paid_at')->nullable()->comment('선금 지급 완료 일시');
            $table->decimal('balance_amount', 15, 2)->nullable()->comment('실제 잔금 지급액');
            $table->timestamp('balance_paid_at')->nullable()->comment('잔금 지급 완료 일시');
            $table->string('payment_status')->comment('대금 지급 상태 (PAYMENT_STATUS_ENUM)');
            $table->timestamps();

            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
        });
        DB::statement("ALTER TABLE contracts ALTER COLUMN payment_status TYPE PAYMENT_STATUS_ENUM USING payment_status::PAYMENT_STATUS_ENUM");
        DB::statement("ALTER TABLE contracts ALTER COLUMN payment_status SET DEFAULT 'pending'");
        DB::statement("COMMENT ON TABLE contracts IS '낙찰된 제안에 대한 계약 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
