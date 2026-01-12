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
        Schema::table('rooms', function (Blueprint $table) {
            // Add room_type_id as foreign key
            $table->foreignId('room_type_id')->nullable()->after('room_number')->constrained('room_types')->onDelete('set null');
            
            // Drop the old room_type string column
            $table->dropColumn('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Add back the old room_type column
            $table->string('room_type')->default('standard')->after('room_number');
            
            // Drop the foreign key and room_type_id column
            $table->dropForeign(['room_type_id']);
            $table->dropColumn('room_type_id');
        });
    }
};
