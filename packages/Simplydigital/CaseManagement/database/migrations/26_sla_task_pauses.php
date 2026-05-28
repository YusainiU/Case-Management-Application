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
        if(!Schema::hasTable('sla_task_pauses')) {
            Schema::create('sla_task_pauses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->unsignedBigInteger('sla_task_id');
                $table->datetime('paused_at');
                $table->datetime('resumed_at')->nullable();
                $table->foreign('sla_task_id')
                    ->references('id')
                    ->on('sla_tasks')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_task_pauses');
    }
};
