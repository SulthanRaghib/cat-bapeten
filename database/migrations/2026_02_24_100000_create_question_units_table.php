<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')
                ->constrained('exam_types')
                ->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['exam_type_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_units');
    }
};
