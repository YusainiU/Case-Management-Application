<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventColumnToActivityLogTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('activity_log', 'attribute_changes')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->string('event')->nullable()->after('subject_type');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('activity_log');
            $table->dropColumn('event');
        });
    }
}
