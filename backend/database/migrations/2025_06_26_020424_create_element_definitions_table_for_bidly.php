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
        Schema::create('element_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('element_type', 100)->unique()->comment('RFP 요소 타입 (VARCHAR 100)'); // VARCHAR로 선언
            $table->string('display_name')->comment('UI에 표시될 요소의 이름');
            $table->text('description')->nullable()->comment('요소에 대한 설명');
            $table->jsonb('input_schema')->default('{}')->comment('해당 요소의 상세 스펙 입력 폼 정의 (JSON Schema)');
            $table->jsonb('default_details_template')->default('{}')->comment('rfp_elements.details 필드에 들어갈 기본 JSON 값 템플릿');
            $table->jsonb('recommended_elements')->default('[]')->comment('이 요소를 선택했을 때 추천할 다른 요소들의 목록');
            $table->timestamps();
        });
        // VARCHAR로 유지 (ENUM 사용하지 않음)
        DB::statement("COMMENT ON TABLE element_definitions IS '운영자가 RFP 요소의 종류, 상세 스펙 입력 필드, 추천 조합 등을 관리하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('element_definitions');
    }
};
