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
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->boolean('budget_allocation')->default(false)->after('is_active');
            $table->boolean('internal_resource_flag')->default(false)->after('budget_allocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_categories', function (Blueprint $table) {
            $table->dropColumn(['budget_allocation', 'internal_resource_flag']);
        });
    }
};
