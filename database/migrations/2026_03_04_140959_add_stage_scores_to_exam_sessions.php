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
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Stores per-stage scores as JSON, e.g. {"wawancara":85,"presentasi":90}
            // Replaces the single interview_score for the new multi-stage assessment flow.
            $table->json('stage_scores')->nullable()->after('interview_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn('stage_scores');
        });
    }
};
