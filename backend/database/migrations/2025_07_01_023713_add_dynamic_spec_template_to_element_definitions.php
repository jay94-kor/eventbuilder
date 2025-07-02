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
        Schema::table('element_definitions', function (Blueprint $table) {
            // 동적 스펙 템플릿 정의
            $table->jsonb('default_spec_template')->default('[]')->comment('기본 스펙 필드 템플릿');
            $table->jsonb('quantity_config')->nullable()->comment('수량 설정 (단위, 최소/최대/권장값, 변형허용여부)');
            $table->jsonb('variant_rules')->nullable()->comment('변형 가능 필드 설정 (허용필드, 최대변형수 등)');
        });
        
        // 성능 최적화를 위한 인덱스 추가
        DB::statement('CREATE INDEX idx_element_definitions_spec_template ON element_definitions USING GIN (default_spec_template)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('element_definitions', function (Blueprint $table) {
            // 인덱스 제거
            DB::statement('DROP INDEX IF EXISTS idx_element_definitions_spec_template');
            
            // 컬럼 제거
            $table->dropColumn([
                'default_spec_template',
                'quantity_config',
                'variant_rules'
            ]);
        });
    }
};
