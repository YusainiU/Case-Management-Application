<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('activity_log', 'attribute_changes')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->json('attribute_changes')->nullable()->after('causer_id');
            });
        }

        if (Schema::hasColumn('activity_log', 'batch_uuid')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('batch_uuid');
            });
        }

        // Migrate existing data from properties to attribute_changes
        DB::table('activity_log')->whereNotNull('properties')->eachById(function ($row) {
            $properties = json_decode($row->properties, true);
            $changes = array_intersect_key($properties, array_flip(['attributes', 'old']));
            $remaining = array_diff_key($properties, array_flip(['attributes', 'old']));

            DB::table('activity_log')->where('id', $row->id)->update([
                'attribute_changes' => empty($changes) ? null : json_encode($changes),
                'properties' => empty($remaining) ? null : json_encode($remaining),
            ]);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('activity_log', 'attribute_changes')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('attribute_changes');
            });
        }

        // You would need to re-add batch_uuid here if you need to rollback
    }
};
