<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Makes role-permission relationships hotel-specific.
     */
    public function up(): void
    {
        // First, drop foreign key constraints that depend on the unique index
        // Get the foreign key constraint names
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'role_permissions' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE `role_permissions` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }

        // Now drop the unique constraint
        try {
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->dropUnique(['role_id', 'permission_id']);
            });
        } catch (\Exception $e) {
            // Unique constraint might not exist, continue
        }

        // Add hotel_id column
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Migrate existing role_permissions relationships
        // Get all hotels
        $hotels = DB::table('hotels')->pluck('id');
        
        // Get all existing role_permissions
        $rolePermissions = DB::table('role_permissions')->whereNull('hotel_id')->get();

        foreach ($hotels as $hotelId) {
            foreach ($rolePermissions as $rp) {
                // Get original role and permission to find their slugs
                $originalRole = DB::table('roles')->where('id', $rp->role_id)->first();
                $originalPermission = DB::table('permissions')->where('id', $rp->permission_id)->first();
                
                if (!$originalRole || !$originalPermission) {
                    continue;
                }
                
                // Find the hotel-specific role and permission by slug and hotel_id
                $role = DB::table('roles')
                    ->where('slug', $originalRole->slug)
                    ->where('hotel_id', $hotelId)
                    ->first();

                $permission = DB::table('permissions')
                    ->where('slug', $originalPermission->slug)
                    ->where('hotel_id', $hotelId)
                    ->first();

                if ($role && $permission) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'hotel_id' => $hotelId,
                        'created_at' => $rp->created_at,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Delete old global role_permissions
        DB::table('role_permissions')->whereNull('hotel_id')->delete();

        // Make hotel_id required
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable(false)->change();
        });

        // Re-add foreign key constraints
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');
                  
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');
        });

        // Add unique constraint on hotel_id + role_id + permission_id
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->unique(['hotel_id', 'role_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique(['hotel_id', 'role_id', 'permission_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn('hotel_id');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->unique(['role_id', 'permission_id']);
        });
    }
};
