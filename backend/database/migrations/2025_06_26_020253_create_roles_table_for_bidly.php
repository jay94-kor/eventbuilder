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
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('agency_id')->comment('해당 역할이 속한 대행사 ID (FK)');
            $table->string('name')->comment('역할 이름 (예: RFP 편집자, 결재자)');
            $table->jsonb('permissions')->default('{}')->comment('이 역할에 부여된 세부 권한 (JSONB)');
            $table->timestamps();

            $table->unique(['agency_id', 'name']); // 대행사 내 역할 이름 고유성
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        DB::statement("COMMENT ON TABLE roles IS '대행사별 역할(Role) 정의 및 관련 권한을 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
