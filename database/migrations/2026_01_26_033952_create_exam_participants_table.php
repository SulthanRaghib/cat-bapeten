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
        Schema::create('exam_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['exam_package_id', 'user_id']);
            // Token should be unique per package generally, or globally? User said "unique per package", implying (exam_package_id, token) unique.
            // But if users access by token, it usually needs to be unique globally or unique enough.
            // Requirement said "unique per package".
            $table->unique(['exam_package_id', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_participants');
    }
};
