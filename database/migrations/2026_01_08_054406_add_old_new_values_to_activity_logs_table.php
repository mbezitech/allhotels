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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Make user_id nullable for system logs
            $table->foreignId('user_id')->nullable()->change();
            
            // Add old_values and new_values columns if they don't exist
            if (!Schema::hasColumn('activity_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('properties');
            }
            if (!Schema::hasColumn('activity_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
            
            // Make model_type and model_id nullable for logs without subjects
            if (Schema::hasColumn('activity_logs', 'model_type')) {
                $table->string('model_type')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'old_values')) {
                $table->dropColumn('old_values');
            }
            if (Schema::hasColumn('activity_logs', 'new_values')) {
                $table->dropColumn('new_values');
            }
        });
    }
};
