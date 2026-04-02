<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->char('pet_id', 36)->primary();
            $table->char('owner_id', 36);
            $table->string('name');
            $table->string('species', 100);
            $table->string('breed', 100);
            $table->unsignedInteger('age');
            $table->string('gender', 20);
            $table->double('weight', 8, 2);
            $table->timestamps();

            $table->foreign('owner_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
