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
        Schema::create('housekeeping_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('area_id')->nullable()->constrained('hotel_areas')->onDelete('set null');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->enum('cleaning_status', ['dirty', 'cleaning', 'clean', 'inspected'])->default('dirty');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated from start/end times
            $table->text('notes')->nullable();
            $table->text('issues_found')->nullable(); // Damages, missing items, etc.
            $table->boolean('has_issues')->default(false);
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
            
            $table->index('hotel_id');
            $table->index('room_id');
            $table->index('area_id');
            $table->index('assigned_to');
            $table->index('cleaning_status');
            $table->index('started_at');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housekeeping_records');
    }
};
