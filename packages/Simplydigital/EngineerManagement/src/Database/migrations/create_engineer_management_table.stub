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
        Schema::create('engineer_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('manager')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->boolean('active')->default(true);
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
                
            $table->foreign('manager')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('engineer_management');
    }
};
