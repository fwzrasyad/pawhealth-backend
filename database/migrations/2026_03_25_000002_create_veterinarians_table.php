<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinarians', function (Blueprint $table) {
            $table->char('vet_id', 36)->primary();
            $table->string('name');
            $table->string('profile_image_url', 500)->default('');
            $table->string('working_hours')->default('');
            $table->json('specialties')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->foreign('vet_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarians');
    }
};
