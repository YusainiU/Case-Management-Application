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
        Schema::create('sla_tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('number');
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->string('task_type')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->unsignedBigInteger('sla_applications_id')->nullable();
            $table->dateTime('respond_by')->nullable();
            $table->dateTime('fix_by')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('fixed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('paused_at')->nullable();
            $table->dateTime('resumed_at')->nullable();
            $table->dateTime('breached_at')->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('respond_by_breach_at')->nullable();
            $table->dateTime('fix_by_breach_at')->nullable();
            $table->string('state')->nullable();
            $table->softDeletes();            

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets');
            
            $table->foreign('sla_applications_id')
                ->references('id')
                ->on('sla_applications');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_tasks');
    }
};
