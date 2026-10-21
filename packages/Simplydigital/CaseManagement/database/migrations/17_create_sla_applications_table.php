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
        if (! Schema::hasTable('sla_applications')) {
            Schema::create('sla_applications', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->string('number');
                $table->string('name');
                $table->string('short_description')->nullable();
                $table->tinyInteger('priority');
                $table->boolean('active')->default(true);
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('service_level_agreement_id')->nullable();

                $table->foreign('customer_id')
                    ->references('id')
                    ->on('customers')
                    ->onDelete('set null');

                $table->foreign('service_level_agreement_id')
                    ->references('id')
                    ->on('service_level_agreements')
                    ->onDelete('set null');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_applications');
    }
};
