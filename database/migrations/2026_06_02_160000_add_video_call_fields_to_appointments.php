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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('consultation_type')->default('in_person');
            $table->string('video_call_channel')->nullable();
            $table->string('video_call_status')->default('none');
            $table->timestamp('video_call_started_at')->nullable();
            $table->timestamp('video_call_ended_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_type',
                'video_call_channel',
                'video_call_status',
                'video_call_started_at',
                'video_call_ended_at',
            ]);
        });
    }
};
