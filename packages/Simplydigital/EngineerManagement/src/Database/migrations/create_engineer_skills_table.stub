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
        Schema::create('engineer_skills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('number')->unique();
            $table->unsignedBigInteger('engineer_id');
            $table->unsignedBigInteger('skill_id');
            $table->string('skill_level')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->softDeletes();

            $table->foreign('engineer_id')
                ->references('id')
                ->on('engineer_management')
                ->cascadeOnDelete();

            $table->foreign('skill_id')
                ->references('id')
                ->on('skills')
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
        Schema::dropIfExists('engineer_skills');
    }
};
