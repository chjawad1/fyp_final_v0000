<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['student', 'supervisor']); // evaluator role type
            $table->enum('status', ['available', 'assigned'])->default('available'); // assigned to a committee or not
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluators');
    }
};