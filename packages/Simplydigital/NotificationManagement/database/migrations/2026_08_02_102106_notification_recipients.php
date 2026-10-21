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
        if (! Schema::hasTable('notification_recipients')) {
            Schema::create('notification_recipients', function (Blueprint $table) {

                $table->id();

                $table->foreignId('notification_id');

                $table->string('type');      // to cc bcc

                $table->string('name')->nullable();

                $table->string('email');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
