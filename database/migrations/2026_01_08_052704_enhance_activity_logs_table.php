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
        // Check if the table exists
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            // Make user_id nullable for system actions (only if it's not already nullable)
            try {
                if (Schema::hasColumn('activity_logs', 'user_id')) {
                    // Check if user_id is already nullable by querying the column definition
                    $columnInfo = DB::select("
                        SELECT IS_NULLABLE 
                        FROM information_schema.COLUMNS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'activity_logs' 
                        AND COLUMN_NAME = 'user_id'
                    ");
                    
                    if (isset($columnInfo[0]) && $columnInfo[0]->IS_NULLABLE === 'NO') {
                        $table->foreignId('user_id')->nullable()->change();
                    }
                }
            } catch (\Exception $e) {
                // If we can't check or change, continue
            }
            
            // Add old_values column if it doesn't exist
            if (!Schema::hasColumn('activity_logs', 'old_values')) {
                try {
                    $table->json('old_values')->nullable()->after('properties');
                } catch (\Exception $e) {
                    // Column might have been added already
                }
            }
            
            // Add new_values column if it doesn't exist
            if (!Schema::hasColumn('activity_logs', 'new_values')) {
                try {
                    $table->json('new_values')->nullable()->after('old_values');
                } catch (\Exception $e) {
                    // Column might have been added already
                }
            }
            
            // Add subject_type and subject_id for better tracking (alias for model_type/model_id but clearer naming)
            // We'll keep model_type/model_id for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            // Drop columns only if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('activity_logs', 'old_values')) {
                $columnsToDrop[] = 'old_values';
            }
            if (Schema::hasColumn('activity_logs', 'new_values')) {
                $columnsToDrop[] = 'new_values';
            }
            
            if (!empty($columnsToDrop)) {
                try {
                    $table->dropColumn($columnsToDrop);
                } catch (\Exception $e) {
                    // Columns might have been dropped already
                }
            }
            
            // Revert user_id to non-nullable (this might fail if there are system logs)
            try {
                if (Schema::hasColumn('activity_logs', 'user_id')) {
                    $columnInfo = DB::select("
                        SELECT IS_NULLABLE 
                        FROM information_schema.COLUMNS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'activity_logs' 
                        AND COLUMN_NAME = 'user_id'
                    ");
                    
                    if (isset($columnInfo[0]) && $columnInfo[0]->IS_NULLABLE === 'YES') {
                        $table->foreignId('user_id')->nullable(false)->change();
                    }
                }
            } catch (\Exception $e) {
                // If there are null values, we can't revert
            }
        });
    }
};
