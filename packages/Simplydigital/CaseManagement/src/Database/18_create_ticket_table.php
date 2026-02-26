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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('number');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('sla_applications_id');
            $table->string('customer_reference')->nullable();
            $table->string('category');
            $table->string('created_by');
            $table->string('source');
            $table->text('list_of_assets')->nullable();
            $table->text('short_description');
            $table->longText('description');
            $table->string('state');
            $table->unsignedBigInteger('raised_by_user')->nullable();
            $table->string('raised_by_nonuser')->nullable();
            $table->string('contact_telephone');
            $table->string('contact_email');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('assigned_group')->nullable();
            $table->boolean('currently_working')->default(false);
            $table->integer('working_time')->default(0);
            $table->string('resolution')->nullable();
            $table->text('resolution_details')->nullable();
            $table->integer('closed_datetime')->nullable();
            $table->integer('fixed_datetime')->nullable();
            $table->integer('resolved_datetime')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('closed_ticket_id')->nullable();
            
            $table->foreign('resolved_by')
            ->references('id')
            ->on('users');            

            $table->foreign('customer_id')
            ->references('id')
            ->on('customers')
            ->onDelete('cascade');
            
            $table->foreign('sla_applications_id')
            ->references('id')
            ->on('sla_applications')
            ->onDelete('set null');
            
            $table->foreign('raised_by_user')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
            

            $table->foreign('assigned_to')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
