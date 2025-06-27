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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('project_name')->comment('행사 이름');
            $table->dateTime('start_datetime')->comment('행사 시작 일시');
            $table->dateTime('end_datetime')->comment('행사 종료 일시');
            $table->dateTime('preparation_start_datetime')->nullable()->comment('준비 시작 일시');
            $table->dateTime('철수_end_datetime')->nullable()->comment('철수 마감 일시');
            $table->string('client_name')->nullable()->comment('클라이언트 이름');
            $table->string('client_contact_person')->nullable()->comment('클라이언트 담당자');
            $table->string('client_contact_number', 20)->nullable()->comment('클라이언트 담당자 연락처');
            $table->uuid('main_agency_contact_user_id')->comment('사내 행사 담당자 (정) ID'); // FK to users
            $table->uuid('sub_agency_contact_user_id')->nullable()->comment('사내 행사 담당자 (부) ID'); // FK to users
            $table->uuid('agency_id')->comment('해당 프로젝트를 생성한 대행사 ID (FK)'); // FK to agencies
            $table->boolean('is_indoor')->comment('행사 실내/실외 여부 (true: 실내, false: 실외)');
            $table->string('location')->comment('행사 장소');
            $table->decimal('budget_including_vat', 15, 2)->nullable()->comment('총 예산 (부가세 포함)');
            $table->timestamps();

            $table->foreign('main_agency_contact_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('sub_agency_contact_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
        });
        DB::statement("COMMENT ON TABLE projects IS '행사의 기본 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
