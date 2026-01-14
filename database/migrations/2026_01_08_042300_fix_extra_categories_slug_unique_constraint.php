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
        if (!Schema::hasTable('extra_categories')) {
            return;
        }

        // Check if the old unique constraint on slug exists
        $slugIndexExists = false;
        try {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'extra_categories' 
                AND index_name = 'extra_categories_slug_unique'
            ");
            $slugIndexExists = isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist
        }

        // Check if the composite unique constraint already exists
        $compositeIndexExists = false;
        try {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'extra_categories' 
                AND index_name = 'extra_categories_hotel_id_slug_unique'
            ");
            $compositeIndexExists = isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If we can't check, try to add it anyway
        }

        Schema::table('extra_categories', function (Blueprint $table) use ($slugIndexExists, $compositeIndexExists) {
            // Drop the old unique constraint on slug if it exists
            if ($slugIndexExists) {
                try {
                    $table->dropUnique(['slug']);
                } catch (\Exception $e) {
                    // Index might have been dropped already
                }
            }
            
            // Add the composite unique constraint if it doesn't exist
            if (!$compositeIndexExists) {
                try {
                    $table->unique(['hotel_id', 'slug']);
                } catch (\Exception $e) {
                    // Constraint might already exist with a different name
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extra_categories', function (Blueprint $table) {
            $table->dropUnique(['hotel_id', 'slug']);
            $table->unique('slug');
        });
    }
};

