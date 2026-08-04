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
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            $table->uuid()->unique();

            $table->string('subject');

            $table->string('channel');

            $table->string('status');

            $table->string('priority');

            $table->string('template')->nullable();

            $table->morphs('trigger');

            $table->json('view_data')->nullable();

            $table->foreignId('created_by')->nullable();

            $table->timestamp('queued_at')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamp('failed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
