<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vet_available_slots', function (Blueprint $table) {
            $table->id();
            $table->char('vet_id', 36);
            $table->dateTime('slot_datetime');
            $table->timestamps();

            $table->foreign('vet_id')->references('vet_id')->on('veterinarians')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_available_slots');
    }
};
