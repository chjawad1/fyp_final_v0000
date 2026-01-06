<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('defence_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('committees')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('venue')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('scheduled_by_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defence_sessions');
    }
};