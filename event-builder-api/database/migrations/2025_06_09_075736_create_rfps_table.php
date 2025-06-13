<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rfps', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // RFP 제목
            $table->string('status')->default('draft'); // RFP 상태
            $table->date('event_date')->nullable(); // 행사 날짜
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 사용자 외래키
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfps');
    }
};
