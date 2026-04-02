<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_routine_logs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('pet_id', 36);
            $table->date('date');
            $table->double('weight', 8, 2);
            $table->text('diet_notes');
            $table->string('activity_level', 50);
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_routine_logs');
    }
};
