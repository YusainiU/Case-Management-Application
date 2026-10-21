<?php

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
        if (! Schema::hasColumn('users_details', 'identification')) {
            Schema::table('users_details', function (Blueprint $table) {
                $table->string('identification')->nullable()->after('avatar');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users_details', 'identification')) {
            Schema::table('users_details', function (Blueprint $table) {
                $table->dropColumn('identification');
            });
        }
    }
};
