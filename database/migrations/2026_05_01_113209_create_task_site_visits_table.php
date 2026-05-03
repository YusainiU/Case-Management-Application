<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_site_visits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('number');
            $table->string('short_description');
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->dateTime('visit_scheduled_at');
            $table->boolean('first_visit')->default(false);
            $table->unsignedBigInteger('scheduled_by');
            $table->text('description')->nullable();
            $table->dateTime('enroute_at')->nullable();
            $table->dateTime('onsite_at')->nullable();
            $table->datetime('offsite_at')->nullable();
            $table->softDeletes();

            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets');

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users');

            $table->foreign('scheduled_by')
                ->references('id')
                ->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_site_visits');
    }
};
