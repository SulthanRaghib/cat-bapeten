<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('question_unit_id')
                ->nullable()
                ->after('exam_type_id')
                ->constrained('question_units')
                ->nullOnDelete();

            $table->foreignId('question_sub_unit_id')
                ->nullable()
                ->after('question_unit_id')
                ->constrained('question_sub_units')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_sub_unit_id');
            $table->dropConstrainedForeignId('question_unit_id');
        });
    }
};
