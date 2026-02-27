<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_unit_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_unit_id')
                ->constrained('question_units')
                ->cascadeOnDelete();
            $table->string('name');              // e.g. "Level 1", "Level 2", "Level 3"
            $table->integer('min_score');
            $table->integer('max_score');
            $table->boolean('is_passing')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_unit_indicators');
    }
};
