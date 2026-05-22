<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revert vet_id to required, add clinic_id, and update status enum
     * for the multi-clinic appointment booking flow.
     *
     * Idempotent: handles partial reruns from previous failed attempts.
     */
    public function up(): void
    {
        $db = config('database.connections.mysql.database');

        // Step 1: Drop the SET NULL FK if it still exists
        if ($this->fkExists($db, 'appointments', 'appointments_vet_id_foreign')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropForeign(['vet_id']));
        }

        // Step 2: Clean up any rows with NULL vet_id from the previous migration era
        DB::table('appointments')->whereNull('vet_id')->delete();

        // Step 3: Change columns
        Schema::table('appointments', function (Blueprint $table) {
            $table->char('vet_id', 36)->nullable(false)->change();
            $table->string('vet_name')->nullable(false)->change();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });

        // Step 4: Re-create vet FK with cascade
        if (!$this->fkExists($db, 'appointments', 'appointments_vet_id_foreign')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->foreign('vet_id')
                      ->references('vet_id')
                      ->on('veterinarians')
                      ->onDelete('cascade');
            });
        }

        // Step 5: Handle clinic_id column
        // Drop if exists from a partial failed run (may have wrong nullability)
        if ($this->fkExists($db, 'appointments', 'appointments_clinic_id_foreign')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropForeign(['clinic_id']));
        }
        if (Schema::hasColumn('appointments', 'clinic_id')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropColumn('clinic_id'));
        }

        // Step 6: Add clinic_id fresh with correct nullable + FK
        Schema::table('appointments', function (Blueprint $table) {
            $table->char('clinic_id', 36)->nullable()->after('appointment_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('clinic_id')
                  ->references('clinic_id')
                  ->on('clinics')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $db = config('database.connections.mysql.database');

        if ($this->fkExists($db, 'appointments', 'appointments_clinic_id_foreign')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropForeign(['clinic_id']));
        }

        if (Schema::hasColumn('appointments', 'clinic_id')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropColumn('clinic_id'));
        }

        if ($this->fkExists($db, 'appointments', 'appointments_vet_id_foreign')) {
            Schema::table('appointments', fn (Blueprint $t) => $t->dropForeign(['vet_id']));
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->char('vet_id', 36)->nullable()->change();
            $table->foreign('vet_id')
                  ->references('vet_id')
                  ->on('veterinarians')
                  ->nullOnDelete();
            $table->string('vet_name')->nullable()->change();
            $table->enum('status', ['pending', 'assigned', 'confirmed', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Check if a foreign key exists using information_schema.
     */
    private function fkExists(string $database, string $table, string $fkName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $fkName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
