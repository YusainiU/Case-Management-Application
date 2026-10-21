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
        if (! Schema::hasTable('notification_attempts')) {
            Schema::create('notification_attempts', function (Blueprint $table) {

                $table->id();

                $table->foreignId('notification_id');

                $table->integer('attempt');

                $table->string('status');

                $table->string('transport');

                $table->string('message_id')->nullable();

                $table->text('response')->nullable();

                $table->text('exception')->nullable();

                $table->timestamp('attempted_at');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_attempts');
    }
};
