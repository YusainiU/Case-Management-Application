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
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('case_id')->constrained('tickets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('week_number');
                $table->unsignedSmallInteger('week_year');
                $table->date('task_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('type', 40);
                $table->text('description');
                $table->string('receipt_path')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('status', 40)->default('awaiting_approval');
                $table->text('rejection_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'week_year', 'week_number']);
                $table->index(['status', 'week_year', 'week_number']);
                $table->index(['case_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
