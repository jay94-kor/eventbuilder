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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('proposal_id')->comment('평가 대상 제안서 ID (FK)');
            $table->uuid('evaluator_user_id')->comment('심사위원 사용자 ID (FK)');
            $table->decimal('price_score', 5, 2)->nullable()->comment('가격 점수 (0-100)');
            $table->decimal('portfolio_score', 5, 2)->nullable()->comment('포트폴리오 점수 (0-100)');
            $table->decimal('additional_score', 5, 2)->nullable()->comment('추가 제안 점수 (0-100)');
            $table->text('comment')->nullable()->comment('심사 의견/코멘트');
            $table->timestamps();

            // 외래 키 제약조건
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
            $table->foreign('evaluator_user_id')->references('id')->on('users')->onDelete('cascade');

            // 한 심사위원은 한 제안서에 대해 한 번만 평가 가능
            $table->unique(['proposal_id', 'evaluator_user_id'])->comment('한 심사위원은 한 제안서에 대해 한 번만 평가 가능');
        });
        DB::statement("COMMENT ON TABLE evaluations IS '심사위원이 제안서에 부여한 점수 및 평가 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
