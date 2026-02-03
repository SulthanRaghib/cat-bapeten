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
        // 1. Modify 'questions' table
        Schema::table('questions', function (Blueprint $table) {
            // Drop foreign key first if it exists
            // Depending on DB driver, dropForeign might need constraint name.
            // Laravel usually guesses it as questions_exam_package_id_foreign
            $table->dropForeign(['exam_package_id']);
            $table->dropColumn('exam_package_id');

            // Add 'type' column
            $table->string('type')->after('id')->index(); // 'technical', 'structural'
        });

        // 2. Create Pivot Table
        Schema::create('exam_package_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Optional: Prevent duplicate linking of same question to same package
            $table->unique(['exam_package_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_package_question');

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('exam_package_id')->nullable()->constrained()->onDelete('cascade');
            $table->dropColumn('type');
        });
    }
};
