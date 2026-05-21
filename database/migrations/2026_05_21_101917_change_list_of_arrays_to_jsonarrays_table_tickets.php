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
            // Add new JSON column
            $table->json('assets')->nullable()->after('list_of_assets');
        });
        
        // Migrate existing data from string to JSON
        $tickets = DB::table('tickets')->get();
        foreach ($tickets as $ticket) {
            $oldData = $ticket->list_of_assets;
            
            // Try to decode if it's already JSON string
            $decoded = json_decode($oldData, true);
            
            // If it's not JSON (maybe comma-separated?), convert it
            if ($decoded === null && !empty($oldData)) {
                // Example: "asset1,asset2,asset3" to array
                $decoded = explode(',', $oldData);
            } elseif (empty($oldData)) {
                $decoded = [];
            }
            
            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['assets' => json_encode($decoded)]);
        }
        
        // Optionally drop the old column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('list_of_assets');
        });
        
        // Rename new column to old name (optional)
        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('assets', 'list_of_assets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('list_of_assets')->change();
        });
    }
};
