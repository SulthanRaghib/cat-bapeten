<?php

declare(strict_types=1);

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
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_participant_id')
                ->constrained('exam_participants')
                ->cascadeOnDelete();
            $table->enum('status', ['ongoing', 'completed', 'terminated'])->default('ongoing');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('total_score')->nullable();
            $table->json('answers_meta')->nullable(); // Stores shuffled question order
            $table->timestamps();

            // Index for quick lookups
            $table->index(['exam_participant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
