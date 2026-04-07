<?php

Namespace Simplydigital\UserManagement\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up() {
        if(!Schema::hasTable('users_statuses')) {
            Schema::create('users_statuses', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->timestamps();
                $table->unsignedBigInteger('user_id');
                $table->boolean('active')->default(true);
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();

            });

        }
    }
    
    public function down()
    {
        Schema::dropIfExists('users_statuses');
    }    

};