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
        Schema::create('assets', function (Blueprint $table) {

            $table->id();
            $table->timestamps();
            $table->string('short_description');
            $table->string('asset_number');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->boolean('active')->default(true);
            $table->decimal('buy_price',8,2)->default(0.00);
            $table->decimal('sell_price',8,2)->default(0.00);
            $table->string('notes')->nullable();
            $table->string('license_number')->nullable();
            $table->string('location')->nullable();
            $table->text('technical_specifications')->nullable();
            $table->string('mac_address')->nullable();
            $table->ipAddress('ip_address')->nullable();


            $table->foreign('supplier_id')
            ->references('id')
            ->on('suppliers')
            ->onDelete('set null');

            $table->foreign('customer_id')
            ->references('id')
            ->on('customers')
            ->onDelete('cascade');
            
            $table->foreign('product_id')
            ->references('id')
            ->on('products')
            ->onDelete('set null');            


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
