<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename daily_routine_logs → health_journals and pivot the columns
     * to support targeted acute recovery tracking (symptom_tags, notes, photo_url).
     */
    public function up(): void
    {
        Schema::rename('daily_routine_logs', 'health_journals');

        Schema::table('health_journals', function (Blueprint $table) {
            // Drop legacy daily-routine columns
            $table->dropColumn(['weight', 'diet_notes', 'activity_level']);

            // Add new health-journal columns
            $table->json('symptom_tags')->nullable()->after('date');
            $table->text('notes')->nullable()->after('symptom_tags');
            $table->string('photo_url', 500)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('health_journals', function (Blueprint $table) {
            $table->dropColumn(['symptom_tags', 'notes', 'photo_url']);

            $table->double('weight', 8, 2)->after('date');
            $table->text('diet_notes')->after('weight');
            $table->string('activity_level', 50)->after('diet_notes');
        });

        Schema::rename('health_journals', 'daily_routine_logs');
    }
};
