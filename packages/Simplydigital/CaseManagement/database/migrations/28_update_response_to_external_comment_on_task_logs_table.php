<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('task_logs')) {
            return;
        }

        if (!Schema::hasColumn(
            'task_logs',
            'response_to_external_comment'
        )) {
            return;
        }

        $foreignKeyExists = DB::table(
            'information_schema.KEY_COLUMN_USAGE'
        )
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'task_logs')
            ->where('COLUMN_NAME', 'response_to_external_comment')
            ->where(
                'CONSTRAINT_NAME',
                'task_logs_response_to_external_comment_foreign'
            )
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        Schema::table('task_logs', function (Blueprint $table) use ($foreignKeyExists) {
            if ($foreignKeyExists) {
                $table->dropForeign(
                    'task_logs_response_to_external_comment_foreign'
                );
            }

            $table->dropColumn('response_to_external_comment');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('task_logs')) {
            return;
        }

        if (Schema::hasColumn(
            'task_logs',
            'response_to_external_comment'
        )) {
            return;
        }

        Schema::table('task_logs', function (Blueprint $table) {
            $table->string('response_to_external_comment')->nullable();
        });

    }
};