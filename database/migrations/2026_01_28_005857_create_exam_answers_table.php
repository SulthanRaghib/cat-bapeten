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
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')
                ->constrained('exam_sessions')
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();
            $table->string('answer')->nullable(); // 'A', 'B', 'C', 'D', etc.
            $table->integer('score')->default(0);
            $table->boolean('is_doubtful')->default(false); // Flag "Ragu-ragu"
            $table->timestamps();

            // Ensure one answer per question per session
            $table->unique(['exam_session_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
