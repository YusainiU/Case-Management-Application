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
        if (! Schema::hasColumn('customer_contacts', 'active')) {
            Schema::table('customer_contacts', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('description');
            });
        }

        if (! Schema::hasColumn('customer_contacts', 'has_portal_access')) {
            Schema::table('customer_contacts', function (Blueprint $table) {
                $table->boolean('has_portal_access')->default(false)->after('active');
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
        foreach (['active', 'has_portal_access'] as $column) {
            if (Schema::hasColumn('customer_contacts', $column)) {
                Schema::table('customer_contacts', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
