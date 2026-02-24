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

        Schema::create('service_level_agreements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('short_description');
            $table->string('sla_key');
            $table->string('start_day')->index();
            $table->string('end_day')->index();
            $table->integer('service_start_time')->default(0);
            $table->integer('service_end_time')->default(0);
            $table->boolean('include_public_holiday')->default(false);
            $table->string('cover_details')->nullable();
            $table->boolean('active')->default(true);
            $table->string('type')->index();
            $table->integer('response_time')->default(0);
            $table->integer('fixed_time')->default(0);            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_level_agreements');
    }
};
