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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('current_phase', 50)->default('idea')->after('status');
            $table->string('semester', 100)->nullable()->after('current_phase');
            $table->boolean('is_late')->default(false)->after('semester');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index(['current_phase', 'semester']);
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['current_phase', 'semester']);
            $table->dropColumn(['current_phase', 'semester', 'is_late']);
        });
    }
};