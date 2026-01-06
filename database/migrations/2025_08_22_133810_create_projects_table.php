<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('projects', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }

    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Foreign key for the student who owns the project
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Foreign key for the assigned supervisor
            // Nullable because a supervisor might not be assigned immediately
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('title');
            $table->text('description');

            // Status can be: pending, approved, rejected, revision_required
            $table->string('status')->default('pending');
            
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
