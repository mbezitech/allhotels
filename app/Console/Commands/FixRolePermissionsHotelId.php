<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRolePermissionsHotelId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:role-permissions-hotel-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix role_permissions table to set hotel_id based on role hotel_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing role_permissions hotel_id...');

        // Get all role_permissions that have null hotel_id or incorrect hotel_id
        $rolePermissions = DB::table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->select('role_permissions.id', 'role_permissions.role_id', 'role_permissions.permission_id', 'roles.hotel_id')
            ->where(function($query) {
                $query->whereNull('role_permissions.hotel_id')
                      ->orWhereColumn('role_permissions.hotel_id', '!=', 'roles.hotel_id');
            })
            ->get();

        $this->info("Found {$rolePermissions->count()} role_permissions entries to fix.");

        $fixed = 0;
        foreach ($rolePermissions as $rp) {
            // Update role_permissions to have the correct hotel_id from the role
            DB::table('role_permissions')
                ->where('id', $rp->id)
                ->update([
                    'hotel_id' => $rp->hotel_id,
                    'updated_at' => now(),
                ]);
            $fixed++;
        }

        $this->info("Fixed {$fixed} role_permissions entries.");

        // Also verify permissions belong to the same hotel
        $mismatched = DB::table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->whereColumn('role_permissions.hotel_id', '!=', 'permissions.hotel_id')
            ->orWhereColumn('role_permissions.hotel_id', '!=', 'roles.hotel_id')
            ->count();

        if ($mismatched > 0) {
            $this->warn("Warning: Found {$mismatched} role_permissions where role, permission, or role_permissions have mismatched hotel_id.");
            $this->warn("These should be cleaned up manually.");
        }

        $this->info('Done!');
        return 0;
    }
}
