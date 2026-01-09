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
        Schema::table('housekeeping_records', function (Blueprint $table) {
            $table->boolean('issue_resolved')->default(false)->after('has_issues');
            $table->timestamp('issue_resolved_at')->nullable()->after('issue_resolved');
            $table->foreignId('issue_resolved_by')->nullable()->constrained('users')->onDelete('set null')->after('issue_resolved_at');
            $table->text('issue_resolution_notes')->nullable()->after('issue_resolved_by');
            
            $table->index('issue_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeeping_records', function (Blueprint $table) {
            $table->dropForeign(['issue_resolved_by']);
            $table->dropIndex(['issue_resolved']);
            $table->dropColumn(['issue_resolved', 'issue_resolved_at', 'issue_resolved_by', 'issue_resolution_notes']);
        });
    }
};
