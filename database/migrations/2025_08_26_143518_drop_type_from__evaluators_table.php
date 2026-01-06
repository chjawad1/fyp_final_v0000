<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('evaluators') && Schema::hasColumn('evaluators', 'type')) {
            Schema::table('evaluators', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        // Optional: add it back if needed
        if (Schema::hasTable('evaluators') && !Schema::hasColumn('evaluators', 'type')) {
            Schema::table('evaluators', function (Blueprint $table) {
                $table->enum('type', ['supervisor'])->default('supervisor')->after('user_id');
            });
        }
    }
};