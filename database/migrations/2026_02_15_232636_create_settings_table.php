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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('string'); // string, integer, boolean, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed initial setting
        DB::table('settings')->insert([
            'key' => 'booking_expiration_minutes',
            'value' => '10',
            'group' => 'bookings',
            'type' => 'integer',
            'description' => 'Time in minutes before an unpaid booking is automatically cancelled.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
