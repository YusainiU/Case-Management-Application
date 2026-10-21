<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_request_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->foreignId('task_log_id')->nullable()->constrained('task_logs')->nullOnDelete();
            $table->timestamps();

            $table->index(['stock_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_request_updates');
    }
};
