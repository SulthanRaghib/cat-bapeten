<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table): void {
            // Change total_score from integer to decimal to support weighted final scores.
            $table->decimal('total_score', 8, 2)->nullable()->change();

            // Raw CBT score (sum of correct answers, 1 pt each).
            $table->decimal('cbt_score', 8, 2)->nullable()->after('total_score');

            // Interview score (0-100 scale, entered manually by assessor).
            $table->decimal('interview_score', 8, 2)->nullable()->after('cbt_score');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table): void {
            $table->dropColumn(['cbt_score', 'interview_score']);
            $table->integer('total_score')->nullable()->change();
        });
    }
};
