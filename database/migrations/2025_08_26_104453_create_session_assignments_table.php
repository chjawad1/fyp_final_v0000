<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('session_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defence_session_id')->constrained('defence_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Evaluation payload (simple rubric for now; can normalize later)
            $table->json('scores_json')->nullable(); // e.g., {"novelty":8,"methodology":7,"presentation":9}
            $table->decimal('total_score', 5, 2)->nullable()->unsigned();
            $table->text('remarks')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['defence_session_id', 'user_id']); // one submission per evaluator
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_assignments');
    }
};