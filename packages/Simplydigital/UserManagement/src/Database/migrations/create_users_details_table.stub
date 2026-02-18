<?php

Namespace Simplydigital\UserManagement\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up() {
        if(!Schema::hasTable('users_details')) {
            Schema::create('users_details', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->unsignedBigInteger('user_id');
                $table->date('date_started')->useCurrent();
                $table->string('address_1');
                $table->string('address_2')->nullable();
                $table->string('county')->nullable();
                $table->string('postcode')->nullable();
                $table->string('country')->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();

            });

        }        
    }

    public function down()
    {
        Schema::dropIfExists('users_details');
    }    

};