<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->char('record_id', 36)->primary();
            $table->char('pet_id', 36);
            $table->char('vet_id', 36);
            $table->text('diagnosis');
            $table->text('treatment');
            $table->date('vaccination_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('attachment_url', 500)->nullable();
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
            $table->foreign('vet_id')->references('vet_id')->on('veterinarians')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
