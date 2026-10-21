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
        if (! Schema::hasColumn('task_logs', 'response_to_internal_comment')) {

            Schema::table('task_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('response_to_internal_comment')
                    ->nullable();

                $table->foreign('response_to_internal_comment')
                    ->references('id')
                    ->on('task_logs')
                    ->nullOnDelete();
            });

        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('task_logs', 'response_to_internal_comment')) {
            Schema::table('task_logs', function (Blueprint $table) {
                $table->dropColumn('response_to_internal_comment');
            });
        }
    }
};
