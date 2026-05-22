<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the clinics table for multi-clinic support.
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->char('clinic_id', 36)->primary();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 50)->default('');
            $table->text('description')->nullable();
            $table->timestamps();

            // Index for location-based queries
            $table->index(['city', 'state']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
