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
        Schema::table('scope_documents', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->after('changelog');
            $table->text('feedback')->nullable()->after('status');
            $table->foreignId('reviewed_by')->nullable()->after('feedback')
                  ->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        Schema::table('scope_documents', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::table('scope_documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['status', 'feedback', 'reviewed_by', 'reviewed_at']);
        });
    }
};