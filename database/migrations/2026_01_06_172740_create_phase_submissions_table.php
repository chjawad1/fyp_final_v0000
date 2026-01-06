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
        Schema::create('phase_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('fyp_phase_id')->constrained('fyp_phases')->onDelete('cascade');
            $table->string('status', 50)->default('not_started');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()
                  ->constrained('users')->onDelete('set null');
            $table->boolean('is_late')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'fyp_phase_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('phase_submissions');
    }
};