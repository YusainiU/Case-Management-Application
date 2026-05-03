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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('number');
            $table->string('name')->nullable();
            $table->string('short_description')->nullable();
            $table->string('type');
            $table->string('source');
            $table->string('external_user')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sla_tasks_id')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->text('description');
            $table->dateTime('notification_sent_at')->nullable();
            $table->boolean('require_attention')->default(false);
            $table->unsignedBigInteger('task_site_visit_id')->nullable();
            $table->string('response_to_external_comment')->nullable();
            $table->softDeletes();
            
            $table->foreign('task_site_visit_id')
            ->references('id')
            ->on('task_site_visits');            
            
            $table->foreign('user_id')
            ->references('id')
            ->on('users');

            $table->foreign('sla_tasks_id')
            ->references('id')
            ->on('sla_tasks');
            
            $table->foreign('ticket_id')
            ->references('id')
            ->on('tickets');            
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};
