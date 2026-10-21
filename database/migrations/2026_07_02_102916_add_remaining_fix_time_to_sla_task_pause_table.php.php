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
        if (! Schema::hasColumn('sla_task_pauses', 'remaining_fix_time')) {
            Schema::table('sla_task_pauses', function (Blueprint $table) {
                $table->integer('remaining_fix_time')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sla_task_pauses', 'remaining_fix_time')) {
            Schema::table('sla_task_pauses', function (Blueprint $table) {
                $table->dropColumn('remaining_fix_time');
            });
        }
    }
};
