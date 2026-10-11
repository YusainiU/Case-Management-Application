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
        if(Schema::hasColumn('sla_task_pauses', 'remaining')) {
            return;
        }
        Schema::table('sla_task_pauses', function (Blueprint $table) {
            $table->integer('remaining')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sla_task_pauses', function (Blueprint $table) {
            $table->dropColumn('remaining');
        });
    }
};
