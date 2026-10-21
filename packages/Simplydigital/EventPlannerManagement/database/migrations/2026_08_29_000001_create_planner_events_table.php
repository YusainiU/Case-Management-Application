<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planner_events')) {
            Schema::create('planner_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
                $table->string('title');
                $table->text('description')
                    ->nullable();
                $table->string('type')
                    ->default('custom');
                $table->dateTime('starts_at');
                $table->dateTime('ends_at')
                    ->nullable();
                $table->boolean('all_day')
                    ->default(false);
                $table->string('colour')
                    ->nullable();
                $table->string('url')
                    ->nullable();
                $table->json('metadata')
                    ->nullable();
                $table->timestamps();
                $table->index([
                    'starts_at',
                    'ends_at',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_events');
    }
};
