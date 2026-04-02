<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->char('appointment_id', 36)->primary();
            $table->char('pet_id', 36);
            $table->string('pet_name');
            $table->char('vet_id', 36);
            $table->string('vet_name');
            $table->text('reason');
            $table->dateTime('appointment_date');
            $table->dateTime('time_slot');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
            $table->foreign('vet_id')->references('vet_id')->on('veterinarians')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
