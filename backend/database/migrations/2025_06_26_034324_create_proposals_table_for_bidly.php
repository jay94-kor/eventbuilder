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
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('announcement_id')->comment('연결된 공고 ID (FK)');
            $table->uuid('vendor_id')->comment('제안서를 제출한 용역사 ID (FK)');
            $table->decimal('proposed_price', 15, 2)->nullable()->comment('용역사가 제안한 금액');
            $table->text('proposal_text')->nullable()->comment('제안서 내용 (텍스트)');
            $table->string('proposal_file_path')->nullable()->comment('첨부된 제안서 파일의 저장 경로');
            $table->string('status')->comment('제안서 상태 (PROPOSAL_STATUS_ENUM)');
            $table->integer('reserve_rank')->nullable()->comment('예비 순위 (1, 2, 3, ...)');
            $table->timestamp('submitted_at')->useCurrent()->comment('제안서 제출 일시');
            $table->timestamps();

            $table->unique(['announcement_id', 'vendor_id'])->comment('한 용역사는 한 공고에 한 번만 제안 가능');

            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
        });
        DB::statement("ALTER TABLE proposals ALTER COLUMN status TYPE PROPOSAL_STATUS_ENUM USING status::PROPOSAL_STATUS_ENUM");
        DB::statement("ALTER TABLE proposals ALTER COLUMN status SET DEFAULT 'submitted'");
        DB::statement("COMMENT ON TABLE proposals IS '용역사가 입찰 공고에 제출한 제안서 정보를 저장하는 테이블'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
