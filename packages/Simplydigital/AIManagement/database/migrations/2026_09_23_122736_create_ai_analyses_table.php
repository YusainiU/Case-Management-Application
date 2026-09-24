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
        Schema::create('ai_analyses', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type');
            $table->string('model')->nullable();
            $table->string('prompt_version')->nullable();
            $table->string('status')->default('pending');
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index([
                'case_id',
                'type',
            ]);

            $table->index([
                'user_id',
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
