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
        Schema::create('agency_approved_vendors', function (Blueprint $table) {
            $table->uuid('agency_id')->comment('대행사 ID (FK)');
            $table->uuid('vendor_id')->comment('승인된 용역사 ID (FK)');
            $table->timestamp('approved_at')->useCurrent()->comment('승인된 일시');
            $table->timestamps();

            $table->primary(['agency_id', 'vendor_id']); // 복합 기본 키

            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
        DB::statement("COMMENT ON TABLE agency_approved_vendors IS '특정 대행사가 승인한 용역사 목록을 관리하여 대행사 전용 채널 접근을 제어하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_approved_vendors');
    }
};
