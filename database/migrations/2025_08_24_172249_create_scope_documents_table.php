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
        Schema::create('scope_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->comment('The admin who uploaded this version')->constrained()->onDelete('cascade');
            $table->string('version'); // e.g., "v1.0", "v1.1", "Final"
            $table->string('file_path'); // Path to the file in storage
            $table->text('changelog')->nullable(); // Description of changes in this version
            $table->timestamps(); // `created_at` will track the upload date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scope_documents');
    }
};