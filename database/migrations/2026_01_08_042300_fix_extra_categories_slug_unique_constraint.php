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
        Schema::table('extra_categories', function (Blueprint $table) {
            // Drop the global unique constraint on slug
            $table->dropUnique(['slug']);
            // Add unique constraint on hotel_id + slug combination
            $table->unique(['hotel_id', 'slug']);
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

