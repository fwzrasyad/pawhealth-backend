<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add clinic_id to users table — links Vets and Managers to their clinic.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('clinic_id', 36)->nullable()->after('phone_number');

            $table->foreign('clinic_id')
                  ->references('clinic_id')
                  ->on('clinics')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};
