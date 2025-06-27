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
        Schema::create('vendors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name')->comment('용역사 이름');
            $table->string('business_registration_number', 20)->unique()->comment('사업자 등록 번호');
            $table->text('address')->nullable()->comment('용역사 주소');
            $table->text('description')->nullable()->comment('용역사 상세 설명');
            $table->jsonb('specialties')->default('[]')->comment('용역사가 제공 가능한 서비스 요소 (JSONB 배열)');
            $table->uuid('master_user_id')->nullable()->comment('용역사 마스터 사용자 ID'); // FK to users
            $table->string('status')->comment('용역사 계정 상태 (VENDOR_ACCOUNT_STATUS_ENUM)'); // STRING으로 선언
            $table->text('ban_reason')->nullable()->comment('계정 제명 사유');
            $table->timestamp('banned_at')->nullable()->comment('계정 제명 일시');
            $table->timestamps();

            $table->foreign('master_user_id')->references('id')->on('users')->onDelete('set null');
        });
        // ENUM 타입으로 컬럼 변경 및 DEFAULT 값 설정
        DB::statement("ALTER TABLE vendors ALTER COLUMN status TYPE VENDOR_ACCOUNT_STATUS_ENUM USING status::VENDOR_ACCOUNT_STATUS_ENUM");
        DB::statement("ALTER TABLE vendors ALTER COLUMN status SET DEFAULT 'active'"); // <--- 추가된 줄
        DB::statement("COMMENT ON TABLE vendors IS '용역사 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
