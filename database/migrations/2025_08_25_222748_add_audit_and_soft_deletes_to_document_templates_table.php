<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            // Soft delete column
            if (!Schema::hasColumn('document_templates', 'deleted_at')) {
                $table->softDeletes();
            }

            // Audit columns
            if (!Schema::hasColumn('document_templates', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('document_templates', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('document_templates', 'deleted_by_id')) {
                $table->foreignId('deleted_by_id')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            if (Schema::hasColumn('document_templates', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            foreach (['created_by_id', 'updated_by_id', 'deleted_by_id'] as $col) {
                if (Schema::hasColumn('document_templates', $col)) {
                    $table->dropForeign(['created_by_id']);
                    $table->dropForeign(['updated_by_id']);
                    $table->dropForeign(['deleted_by_id']);
                    $table->dropColumn($col);
                }
            }
        });
    }
};