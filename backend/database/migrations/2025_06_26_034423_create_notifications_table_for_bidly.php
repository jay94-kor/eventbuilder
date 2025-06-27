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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('user_id')->comment('알림을 받을 사용자 ID (FK)');
            $table->string('type')->comment('알림 타입 (NOTIFICATION_TYPE_ENUM)'); // STRING으로 선언
            $table->text('message')->comment('알림 메시지 내용');
            $table->boolean('is_read')->default(false)->comment('알림 확인 여부');
            $table->uuid('related_id')->nullable()->comment('관련 레코드의 ID (예: rfp_id, announcement_id)');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        // ENUM 타입으로 컬럼 변경
        DB::statement("ALTER TABLE notifications ALTER COLUMN type TYPE NOTIFICATION_TYPE_ENUM USING type::NOTIFICATION_TYPE_ENUM");
        DB::statement("COMMENT ON TABLE notifications IS '사용자에게 전송되는 알림 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
