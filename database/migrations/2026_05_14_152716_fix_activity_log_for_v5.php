<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Add the missing column
            $table->json('attribute_changes')->nullable();
            
            // Drop the v4 column
            $table->dropColumn('batch_uuid');
        });
    }

    public function down()
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('attribute_changes');
            $table->char('batch_uuid', 36)->nullable();
        });
    }
};
