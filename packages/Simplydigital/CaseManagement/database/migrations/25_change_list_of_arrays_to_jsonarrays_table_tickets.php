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
        if (Schema::hasColumn('tickets', 'list_of_assets') && ! Schema::hasColumn('tickets', 'assets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('assets')->nullable()->after('list_of_assets');
            });
        }

        if (Schema::hasColumn('tickets', 'list_of_assets') && Schema::hasColumn('tickets', 'assets')) {
            $tickets = DB::table('tickets')->get();
            foreach ($tickets as $ticket) {
                $oldData = $ticket->list_of_assets;
                $decoded = json_decode($oldData, true);

                if ($decoded === null && ! empty($oldData)) {
                    $decoded = explode(',', $oldData);
                } elseif (empty($oldData)) {
                    $decoded = [];
                }

                DB::table('tickets')
                    ->where('id', $ticket->id)
                    ->update(['assets' => json_encode($decoded)]);
            }

            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('list_of_assets');
            });
        }

        if (Schema::hasColumn('tickets', 'assets') && ! Schema::hasColumn('tickets', 'list_of_assets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->renameColumn('assets', 'list_of_assets');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'list_of_assets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('list_of_assets')->change();
            });
        }
    }
};
