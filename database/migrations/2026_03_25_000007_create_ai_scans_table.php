<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_scans', function (Blueprint $table) {
            $table->char('scan_id', 36)->primary();
            $table->char('pet_id', 36);
            $table->dateTime('scan_date');
            $table->string('image_url', 500);
            $table->string('ai_result_label');
            $table->double('confidence_score', 5, 4);
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_scans');
    }
};
