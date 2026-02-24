<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_sub_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_unit_id')
                ->constrained('question_units')
                ->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['question_unit_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_sub_units');
    }
};
