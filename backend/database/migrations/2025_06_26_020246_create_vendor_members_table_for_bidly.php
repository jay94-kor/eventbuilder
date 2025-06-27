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
        Schema::create('vendor_members', function (Blueprint $table) {
            $table->uuid('user_id')->comment('사용자 ID (FK)');
            $table->uuid('vendor_id')->comment('용역사 ID (FK)');
            $table->string('position')->nullable()->comment('용역사 내 직책');
            $table->timestamp('joined_at')->useCurrent()->comment('용역사에 합류한 일시');
            $table->timestamps();

            $table->primary(['user_id', 'vendor_id']); // 복합 기본 키

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
        DB::statement("COMMENT ON TABLE vendor_members IS '사용자와 용역사를 연결하고, 용역사 내에서의 멤버 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_members');
    }
};
