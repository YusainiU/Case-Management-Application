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
        $hasForeignKey = collect(Schema::getForeignKeys('task_logs'))
            ->contains(fn (array $foreignKey): bool => in_array('response_to_external_comment', $foreignKey['columns'], true));

        if (Schema::hasColumn('task_logs', 'response_to_external_comment') && ! $hasForeignKey) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->dropColumn('response_to_external_comment');
            });
        }

        if (! $hasForeignKey && ! Schema::hasColumn('task_logs', 'response_to_external_comment')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->foreignId('response_to_external_comment')
                    ->nullable()
                    ->constrained('task_logs')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasForeignKey = collect(Schema::getForeignKeys('task_logs'))
            ->contains(fn (array $foreignKey): bool => in_array('response_to_external_comment', $foreignKey['columns'], true));

        if ($hasForeignKey) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->dropForeign(['response_to_external_comment']);
                $table->dropColumn('response_to_external_comment');
            });
        } elseif (Schema::hasColumn('task_logs', 'response_to_external_comment')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->dropColumn('response_to_external_comment');
            });
        }

        if (! Schema::hasColumn('task_logs', 'response_to_external_comment')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->string('response_to_external_comment')
                    ->nullable();
            });
        }
    }
};
