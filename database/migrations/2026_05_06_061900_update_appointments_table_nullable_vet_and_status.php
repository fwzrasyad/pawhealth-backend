<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make vet_id nullable and update status enum to include 'assigned'.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Allow appointments to be created without a vet assigned
            $table->char('vet_id', 36)->nullable()->change();

            // Drop the existing foreign key so we can re-create it with nullOnDelete
            $table->dropForeign(['vet_id']);
            $table->foreign('vet_id')
                  ->references('vet_id')
                  ->on('veterinarians')
                  ->nullOnDelete();

            // Make vet_name nullable as well (no vet at creation time)
            $table->string('vet_name')->nullable()->change();

            // Expand status enum to include 'assigned' state
            $table->enum('status', ['pending', 'assigned', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->char('vet_id', 36)->nullable(false)->change();

            $table->dropForeign(['vet_id']);
            $table->foreign('vet_id')
                  ->references('vet_id')
                  ->on('veterinarians')
                  ->onDelete('cascade');

            $table->string('vet_name')->nullable(false)->change();

            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }
};
