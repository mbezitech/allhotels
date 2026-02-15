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

        // Try to drop the old unique constraint on slug if it exists
        try {
            Schema::table('extra_categories', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception $e) {
            // Index might not exist or already dropped
        }
        
        // Try to add the composite unique constraint if it doesn't exist
        try {
            Schema::table('extra_categories', function (Blueprint $table) {
                $table->unique(['hotel_id', 'slug'], 'extra_categories_hotel_id_slug_unique');
            });
        } catch (\Exception $e) {
            // Constraint might already exist
        }
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

