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
        Schema::table('task_logs', function (Blueprint $table) {
            // Drop the existing varchar column
            $table->dropColumn('response_to_external_comment');
        });

        Schema::table('task_logs', function (Blueprint $table) {
            // Recreate as a self-referencing foreign key
            $table->foreignId('response_to_external_comment')
                ->nullable()
                ->constrained('task_logs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->dropForeign(['response_to_external_comment']);
            $table->dropColumn('response_to_external_comment');
        });

        Schema::table('task_logs', function (Blueprint $table) {
            $table->string('response_to_external_comment')
                ->nullable();
        });
    }
};
