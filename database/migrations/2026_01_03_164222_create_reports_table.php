<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // 'defence_sessions', 'projects', 'users', 'evaluations'
            $table->json('parameters')->nullable(); // Store filter parameters
            $table->string('format'); // 'pdf', 'excel', 'csv'
            $table->string('status')->default('pending'); // 'pending', 'generating', 'completed', 'failed'
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};