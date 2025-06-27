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
        Schema::create('agencies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name')->comment('대행사 이름');
            $table->string('business_registration_number', 20)->unique()->comment('사업자 등록 번호');
            $table->text('address')->nullable()->comment('대행사 주소');
            $table->uuid('master_user_id')->nullable()->comment('해당 대행사를 관리하는 마스터 사용자 ID'); // FK to users
            $table->string('subscription_status')->comment('대행사 구독 상태 (SUBSCRIPTION_STATUS_ENUM)'); // STRING으로 선언
            $table->timestamp('subscription_end_date')->nullable()->comment('구독 만료일');
            $table->timestamps();

            $table->foreign('master_user_id')->references('id')->on('users')->onDelete('set null');
        });
        // ENUM 타입으로 컬럼 변경 및 DEFAULT 값 설정
        DB::statement("ALTER TABLE agencies ALTER COLUMN subscription_status TYPE SUBSCRIPTION_STATUS_ENUM USING subscription_status::SUBSCRIPTION_STATUS_ENUM");
        DB::statement("ALTER TABLE agencies ALTER COLUMN subscription_status SET DEFAULT 'inactive'"); // <--- 추가된 줄
        DB::statement("COMMENT ON TABLE agencies IS '대행사 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
