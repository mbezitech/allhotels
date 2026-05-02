<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
            ['email' => 'innocentfrancismhina@gmail.com'],
            [
                'name' => 'Innocent Francis Mhina',
                'password' => Hash::make('SuperAdmin2026!'),
                'is_super_admin' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('email', 'innocentfrancismhina@gmail.com')
            ->update(['is_super_admin' => false]);
    }
};
