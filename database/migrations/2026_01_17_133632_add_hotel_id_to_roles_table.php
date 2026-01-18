<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes roles hotel-specific - each hotel has its own isolated roles.
     */
    public function up(): void
    {
        // Drop the unique constraint on slug first
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Add hotel_id column (nullable initially for data migration)
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Migrate existing roles: assign to all hotels
        // This ensures existing roles are available to all hotels
        $hotels = DB::table('hotels')->pluck('id');
        $roles = DB::table('roles')->whereNull('hotel_id')->get();

        foreach ($hotels as $hotelId) {
            foreach ($roles as $role) {
                // Create a copy of each role for each hotel
                // Slug remains the same - uniqueness is enforced by hotel_id + slug
                DB::table('roles')->insert([
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'hotel_id' => $hotelId,
                    'created_at' => $role->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete old global roles
        DB::table('roles')->whereNull('hotel_id')->delete();

        // Make hotel_id required
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable(false)->change();
        });

        // Add unique constraint on hotel_id + slug
        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['hotel_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['hotel_id', 'slug']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['slug']);
        });
    }
};
