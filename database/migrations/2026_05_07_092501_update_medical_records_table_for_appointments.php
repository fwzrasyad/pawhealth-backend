<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Overhaul the medical_records table to link directly to Appointments
     * and carry structured clinical data (doctor_notes, medications, follow-up).
     */
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Link to appointment (nullable — records can exist without one)
            $table->char('appointment_id', 36)->nullable()->after('vet_id');
            $table->foreign('appointment_id')
                  ->references('appointment_id')
                  ->on('appointments')
                  ->onDelete('set null');

            // New structured clinical columns
            $table->text('doctor_notes')->nullable()->after('diagnosis');
            $table->json('medications_prescribed')->nullable()->after('doctor_notes');
            $table->text('follow_up_instructions')->nullable()->after('medications_prescribed');

            // Drop legacy columns that are being replaced
            $table->dropColumn(['treatment', 'vaccination_date', 'next_due_date', 'attachment_url']);
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->text('treatment')->after('diagnosis');
            $table->date('vaccination_date')->nullable()->after('treatment');
            $table->date('next_due_date')->nullable()->after('vaccination_date');
            $table->string('attachment_url', 500)->nullable()->after('next_due_date');

            $table->dropForeign(['appointment_id']);
            $table->dropColumn(['appointment_id', 'doctor_notes', 'medications_prescribed', 'follow_up_instructions']);
        });
    }
};
