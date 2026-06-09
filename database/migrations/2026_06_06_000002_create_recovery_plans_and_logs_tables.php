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
        Schema::create('recovery_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pet_id');
            $table->uuid('appointment_id')->nullable();
            $table->uuid('vet_id');
            
            $table->text('instructions');
            $table->integer('duration_days')->default(7);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            
            $table->timestamps();

            $table->foreign('pet_id')->references('pet_id')->on('pets')->onDelete('cascade');
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('set null');
            $table->foreign('vet_id')->references('vet_id')->on('veterinarians')->onDelete('cascade');
        });

        Schema::create('recovery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recovery_plan_id');
            
            $table->date('date');
            $table->json('symptom_status')->nullable()->comment('e.g. {"eating": "good", "pain": "mild"}');
            $table->text('owner_notes')->nullable();
            $table->string('photo_url')->nullable();
            
            $table->timestamps();

            $table->foreign('recovery_plan_id')->references('id')->on('recovery_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_logs');
        Schema::dropIfExists('recovery_plans');
    }
};
