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
        Schema::create('agency_members', function (Blueprint $table) {
            $table->uuid('user_id')->comment('사용자 ID (FK)');
            $table->uuid('agency_id')->comment('대행사 ID (FK)');
            $table->string('position')->nullable()->comment('대행사 내 직책');
            $table->jsonb('permissions')->default('{}')->comment('해당 멤버에게 부여된 세부 권한 (JSONB)');
            $table->timestamp('joined_at')->useCurrent()->comment('대행사에 합류한 일시');
            $table->timestamps();

            $table->primary(['user_id', 'agency_id']); // 복합 기본 키

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        DB::statement("COMMENT ON TABLE agency_members IS '사용자와 대행사를 연결하고, 대행사 내에서의 멤버 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_members');
    }
};
