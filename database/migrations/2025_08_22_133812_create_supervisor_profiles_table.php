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
    //     Schema::create('supervisor_profiles', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }

    public function up(): void
    {
        Schema::create('supervisor_profiles', function (Blueprint $table) {
            $table->id();

            // A profile belongs to a user (the supervisor)
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            $table->text('research_interests')->nullable();
            $table->integer('available_slots')->default(8); // As per BR-2

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_profiles');
    }
};
