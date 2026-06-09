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
        Schema::create('vaccination_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pet_id');
            $table->uuid('record_id')->nullable()->comment('Linked to a specific medical record/visit if administered during one');
            $table->uuid('administered_by_vet_id')->nullable();
            
            $table->string('vaccine_name');
            $table->boolean('is_core')->default(false);
            $table->date('date_administered');
            $table->date('next_due_date')->nullable();
            
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
            $table->foreign('record_id')->references('record_id')->on('medical_records')->onDelete('set null');
            $table->foreign('administered_by_vet_id')->references('vet_id')->on('veterinarians')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};
