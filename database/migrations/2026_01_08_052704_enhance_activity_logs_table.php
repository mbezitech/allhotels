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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Make user_id nullable for system actions
            $table->foreignId('user_id')->nullable()->change();
            
            // Add old_values and new_values columns
            $table->json('old_values')->nullable()->after('properties');
            $table->json('new_values')->nullable()->after('old_values');
            
            // Add subject_type and subject_id for better tracking (alias for model_type/model_id but clearer naming)
            // We'll keep model_type/model_id for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Note: Making user_id non-nullable again might fail if there are system logs
            // So we'll just drop the new columns
            $table->dropColumn(['old_values', 'new_values']);
            
            // Revert user_id to non-nullable (this might fail if system logs exist)
            try {
                $table->foreignId('user_id')->nullable(false)->change();
            } catch (\Exception $e) {
                // If there are null values, we can't revert
            }
        });
    }
};
