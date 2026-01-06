<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'completed' to the list of allowed ENUM values
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the ENUM list back to its original state
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};