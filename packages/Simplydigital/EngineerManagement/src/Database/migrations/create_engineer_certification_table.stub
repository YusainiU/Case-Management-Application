<?php

namespace Simplydigital\EngineerManagement\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('engineer_certifications', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->timestamps();                
            $table->string('certification_number');
            $table->unsignedBigInteger('certificate_id');
            $table->unsignedBigInteger('engineer_id');
            $table->datetime('issued_date')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->boolean('expired')->default(false);
            $table->boolean('active')->default(true);
            $table->softDeletes();

            $table->foreign('certificate_id')
            ->references('id')
            ->on('certificates')
            ->cascadeOnDelete();

            $table->foreign('engineer_id')
            ->references('id')
            ->on('engineer_management')
            ->cascadeOnDelete();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
