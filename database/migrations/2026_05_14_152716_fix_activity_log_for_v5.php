<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('activity_log', 'attribute_changes')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->json('attribute_changes')->nullable();
            });
        }

        if (Schema::hasColumn('activity_log', 'batch_uuid')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('batch_uuid');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('activity_log', 'attribute_changes')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('attribute_changes');
            });
        }

        if (! Schema::hasColumn('activity_log', 'batch_uuid')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->char('batch_uuid', 36)->nullable();
            });
        }
    }
};
