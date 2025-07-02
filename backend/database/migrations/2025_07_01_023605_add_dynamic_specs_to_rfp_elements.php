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
        Schema::table('rfp_elements', function (Blueprint $table) {
            // 수량 관리
            $table->integer('total_quantity')->default(1)->comment('총 수량');
            $table->integer('base_quantity')->default(1)->comment('기본 스펙 적용 수량');
            $table->boolean('use_variants')->default(false)->comment('스펙 변형 사용 여부');
            
            // 동적 스펙 시스템
            $table->jsonb('spec_fields')->default('[]')->comment('동적 스펙 필드들');
            $table->jsonb('spec_variants')->default('[]')->comment('스펙 변형들');
        });
        
        // 성능 최적화를 위한 인덱스 추가
        DB::statement('CREATE INDEX idx_rfp_elements_spec_fields ON rfp_elements USING GIN (spec_fields)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfp_elements', function (Blueprint $table) {
            // 인덱스 제거
            DB::statement('DROP INDEX IF EXISTS idx_rfp_elements_spec_fields');
            
            // 컬럼 제거
            $table->dropColumn([
                'total_quantity',
                'base_quantity', 
                'use_variants',
                'spec_fields',
                'spec_variants'
            ]);
        });
    }
};
