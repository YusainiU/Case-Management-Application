<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_action_proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('case_id')->index();
            $table->unsignedBigInteger('ai_analysis_id')->nullable()->index();
            $table->string('action_type', 64);
            $table->json('payload');
            $table->string('source_hash', 64);
            $table->string('payload_hash', 64);
            $table->text('reason');
            $table->string('status', 32)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['status', 'expires_at']);
        });

        Schema::create('ai_action_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('proposal_id')->index();
            $table->unsignedBigInteger('reviewer_id');
            $table->string('decision', 20);
            $table->string('payload_hash', 64);
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->foreign('proposal_id')->references('id')
                ->on('ai_action_proposals')->cascadeOnDelete();
        });

        Schema::create('ai_action_executions', function (Blueprint $table) {
            $table->id();
            $table->uuid('proposal_id')->unique();
            $table->string('status', 32);
            $table->text('error')->nullable();
            $table->timestamps();
            $table->foreign('proposal_id')->references('id')
                ->on('ai_action_proposals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_action_executions');
        Schema::dropIfExists('ai_action_approvals');
        Schema::dropIfExists('ai_action_proposals');
    }
};
