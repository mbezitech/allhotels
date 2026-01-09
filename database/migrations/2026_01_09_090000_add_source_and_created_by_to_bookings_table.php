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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('source')->default('dashboard')->after('status');
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
            $table->index('source');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['source', 'created_by']);
        });
    }
};


