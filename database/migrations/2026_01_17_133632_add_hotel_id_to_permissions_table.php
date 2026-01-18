<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes permissions hotel-specific - each hotel has its own isolated permissions.
     */
    public function up(): void
    {
        // Drop the unique constraint on slug first
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Add hotel_id column (nullable initially for data migration)
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Migrate existing permissions: assign to all hotels
        // This ensures existing permissions are available to all hotels
        $hotels = DB::table('hotels')->pluck('id');
        $permissions = DB::table('permissions')->whereNull('hotel_id')->get();

        foreach ($hotels as $hotelId) {
            foreach ($permissions as $permission) {
                // Create a copy of each permission for each hotel
                DB::table('permissions')->insert([
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'description' => $permission->description,
                    'hotel_id' => $hotelId,
                    'created_at' => $permission->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete old global permissions
        DB::table('permissions')->whereNull('hotel_id')->delete();

        // Make hotel_id required
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable(false)->change();
        });

        // Add unique constraint on hotel_id + slug
        Schema::table('permissions', function (Blueprint $table) {
            $table->unique(['hotel_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['hotel_id', 'slug']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->unique(['slug']);
        });
    }
};
