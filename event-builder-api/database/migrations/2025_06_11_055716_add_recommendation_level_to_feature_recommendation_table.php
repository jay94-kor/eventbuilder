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
        Schema::table('feature_recommendation', function (Blueprint $table) {
            $table->string('level')->default('R1')->after('recommended_feature_id');
            $table->integer('priority')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_recommendation', function (Blueprint $table) {
            $table->dropColumn(['level', 'priority']);
        });
    }
};
