<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('number');
                $table->timestamps();
                $table->string('name')->nullable(false);
                $table->string('short_description')->nullable();
                $table->string('primary_type')->nullable();
                $table->string('url')->nullable();
                $table->string('address_1')->nullable();
                $table->string('address_2')->nullable();
                $table->string('address_3')->nullable();
                $table->string('telephone_1')->nullable();
                $table->string('telephone_2')->nullable();
                $table->string('telephone_3')->nullable();
                $table->string('child_type')->nullable();
                $table->integer('parent_company')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('portal_enabled')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
