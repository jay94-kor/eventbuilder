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
        Schema::create('event_basics', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('event_title');
            $table->string('event_location');
            $table->string('venue_type'); // 실내, 실외, 혼합
            $table->json('zones')->nullable(); // 각 존별 이름, 타입, 수량
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->boolean('is_total_budget_undecided')->default(false);
            $table->date('event_start_date_range_min')->nullable();
            $table->date('event_start_date_range_max')->nullable();
            $table->date('event_end_date_range_min')->nullable();
            $table->date('event_end_date_range_max')->nullable();
            $table->integer('event_duration_days')->nullable();
            $table->date('setup_start_date')->nullable();
            $table->date('teardown_end_date')->nullable();
            $table->date('project_kickoff_date');
            $table->date('settlement_close_date');
            $table->string('contact_person_name');
            $table->string('contact_person_contact');
            $table->string('admin_person_name');
            $table->string('admin_person_contact');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_basics');
    }
};
