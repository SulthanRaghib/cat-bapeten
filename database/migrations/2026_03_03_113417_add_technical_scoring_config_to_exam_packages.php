<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_packages', function (Blueprint $table): void {
            // Stores interview/CBT weighting config for Teknis exam packages.
            // Example: {"has_interview": true, "cbt_weight": 60, "interview_weight": 40}
            $table->json('technical_scoring_config')
                ->nullable()
                ->after('unit_scoring_configs');
        });
    }

    public function down(): void
    {
        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->dropColumn('technical_scoring_config');
        });
    }
};
