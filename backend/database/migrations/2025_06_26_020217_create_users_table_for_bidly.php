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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('email')->unique()->comment('사용자 이메일 (로그인 ID)');
            $table->string('password')->comment('비밀번호 (해시값)');
            $table->string('name')->comment('사용자 이름');
            $table->string('phone_number', 20)->nullable()->comment('사용자 전화번호');
            $table->string('user_type')->comment('사용자 타입 (USER_TYPE_ENUM)'); // STRING으로 선언
            $table->rememberToken();
            $table->timestamps();
        });
        // ENUM 타입으로 컬럼 변경
        DB::statement("ALTER TABLE users ALTER COLUMN user_type TYPE USER_TYPE_ENUM USING user_type::USER_TYPE_ENUM");
        DB::statement("COMMENT ON TABLE users IS '모든 시스템 사용자 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
