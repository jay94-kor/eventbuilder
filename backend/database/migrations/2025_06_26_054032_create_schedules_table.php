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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuidMorphs('schedulable'); // 스케줄이 속한 엔티티 (프로젝트 또는 공고) - schedulable_id (UUID), schedulable_type (string)
            $table->string('title')->comment('스케줄 제목');
            $table->text('description')->nullable()->comment('스케줄 상세 설명');
            $table->timestamp('start_datetime')->comment('스케줄 시작 일시');
            $table->timestamp('end_datetime')->comment('스케줄 종료 일시');
            $table->string('location')->nullable()->comment('스케줄 장소');
            $table->string('status')->nullable()->comment('스케줄 상태'); // 기본값 제거하고 nullable로 설정
            $table->timestamps();
        });
        
        // ENUM 타입 생성 및 컬럼 변경
        DB::statement("CREATE TYPE SCHEDULE_STATUS_ENUM AS ENUM ('planned', 'ongoing', 'completed', 'cancelled')");
        DB::statement("ALTER TABLE schedules ALTER COLUMN status TYPE SCHEDULE_STATUS_ENUM USING status::SCHEDULE_STATUS_ENUM");
        DB::statement("ALTER TABLE schedules ALTER COLUMN status SET DEFAULT 'planned'"); // 타입 변경 후 기본값 설정
        DB::statement("ALTER TABLE schedules ALTER COLUMN status SET NOT NULL"); // NOT NULL 제약 추가
        DB::statement("COMMENT ON TABLE schedules IS '프로젝트 또는 공고에 대한 스케줄 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
        DB::statement("DROP TYPE IF EXISTS SCHEDULE_STATUS_ENUM"); // ENUM 타입도 삭제
    }
};
