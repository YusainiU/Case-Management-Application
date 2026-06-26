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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dateTime('first_case_assignment')
                ->nullable();
        });

        //Use updated_at as a default date for existing record if exist
        $tickets = DB::table('tickets')->get();
        if ($tickets->isNotEmpty()) {
            foreach ($tickets as $ticket) {
                $lastUpdated = $ticket->updated_at;
                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['first_case_assignment' => $lastUpdated]);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('first_case_assignment');
        });
    }
};
