<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL: This migration ensures that activity logs are NEVER deleted when a hotel is deleted.
     * The foreign key constraint is changed from 'cascade' to 'set null', and hotel_id is made nullable.
     * This preserves the complete audit trail even after hotels are deleted.
     */
    public function up(): void
    {
        // Get the actual foreign key constraint name from the database
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'activity_logs' 
            AND COLUMN_NAME = 'hotel_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            
            // Drop the foreign key using raw SQL to avoid Laravel's naming issues
            DB::statement("ALTER TABLE `activity_logs` DROP FOREIGN KEY `{$constraintName}`");
        }

        // Check if hotel_id is already nullable
        $columnInfo = DB::select("
            SELECT IS_NULLABLE 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'activity_logs' 
            AND COLUMN_NAME = 'hotel_id'
        ");

        if (!empty($columnInfo) && $columnInfo[0]->IS_NULLABLE === 'NO') {
            // Make hotel_id nullable to preserve logs when hotels are deleted
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('hotel_id')->nullable()->change();
            });
        }

        // Re-add foreign key with set null on delete to preserve audit trail
        // This ensures logs are NEVER deleted when hotels are deleted
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('hotel_id')
                  ->references('id')
                  ->on('hotels')
                  ->onDelete('set null'); // CRITICAL: set null, NOT cascade
        });
    }

    /**
     * Reverse the migrations.
     * WARNING: This will change back to cascade, which will delete logs when hotels are deleted.
     */
    public function down(): void
    {
        // Get the actual foreign key constraint name from the database
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'activity_logs' 
            AND COLUMN_NAME = 'hotel_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            
            // Drop the foreign key using raw SQL
            DB::statement("ALTER TABLE `activity_logs` DROP FOREIGN KEY `{$constraintName}`");
        }

        // Make hotel_id not nullable again (only if there are no null values)
        $nullCount = DB::table('activity_logs')->whereNull('hotel_id')->count();
        if ($nullCount === 0) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('hotel_id')->nullable(false)->change();
            });
        }

        // Re-add foreign key with cascade (WARNING: This will delete logs when hotels are deleted)
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('hotel_id')
                  ->references('id')
                  ->on('hotels')
                  ->onDelete('cascade');
        });
    }
};
