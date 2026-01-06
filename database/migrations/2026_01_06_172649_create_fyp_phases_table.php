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
        Schema::create('fyp_phases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('semester', 100);
            $table->integer('order')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->boolean('allow_late')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['semester', 'order']);
            $table->index(['semester', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fyp_phases');
    }
};