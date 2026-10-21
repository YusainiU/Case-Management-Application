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
        if (! Schema::hasTable('uploaded_files')) {
            Schema::create('uploaded_files', function (Blueprint $table) {
                $table->id();

                /*
                 * The entity this file belongs to.
                 *
                 * Examples:
                 *   Cases
                 *   Customers
                 *   Assets
                 *   Suppliers
                 */
                $table->string('owner_type', 100);

                /*
                 * The business identifier of the owner.
                 *
                 * Examples:
                 *   CASE-000123
                 *   CUST-000456
                 */
                $table->string('owner_identifier', 191);

                /*
                 * Laravel filesystem disk.
                 *
                 * Examples:
                 *   local
                 *   public
                 *   s3
                 */
                $table->string('disk', 100);

                /*
                 * Relative path on the configured filesystem disk.
                 *
                 * Example:
                 *   uploads/Cases/CASE-000123/01JXYZ...pdf
                 */
                $table->text('path');

                /*
                 * Filename supplied by the user.
                 */
                $table->string('original_name');

                /*
                 * Actual filename stored on disk.
                 */
                $table->string('stored_name');

                /*
                 * MIME type detected/stored for the file.
                 *
                 * Example:
                 *   application/pdf
                 */
                $table->string('mime_type', 191)->nullable();

                /*
                 * File extension.
                 *
                 * Example:
                 *   pdf
                 */
                $table->string('extension', 20)->nullable();

                /*
                 * File size in bytes.
                 */
                $table->unsignedBigInteger('size');

                /*
                 * Optional checksum for integrity/duplicate detection.
                 */
                $table->string('checksum', 128)->nullable();

                /*
                 * Additional package/application-specific information.
                 */
                $table->json('metadata')->nullable();

                /*
                 * User who uploaded the file.
                 *
                 * This deliberately isn't a foreign key because
                 * UserManagement may be an independent package.
                 */
                $table->unsignedBigInteger('uploaded_by')->nullable();

                $table->timestamps();

                /*
                 * Most queries will be:
                 *
                 *   WHERE owner_type = ?
                 *   AND owner_identifier = ?
                 */
                $table->index(
                    ['owner_type', 'owner_identifier'],
                    'uploaded_files_owner_index'
                );

                /*
                 * Useful when looking up files by checksum.
                 */
                $table->index(
                    'checksum',
                    'uploaded_files_checksum_index'
                );

                /*
                 * Useful for auditing/listing files uploaded by a user.
                 */
                $table->index(
                    'uploaded_by',
                    'uploaded_files_uploaded_by_index'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
