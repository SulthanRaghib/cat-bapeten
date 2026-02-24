<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                    // e.g. "Teknis", "Mansoskul"
            $table->string('code')->unique();          // e.g. "TEK", "MAN"
            $table->string('evaluation_method');        // 'correct_wrong' | 'weighted'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
