<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selection_stage_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                          // "Wawancara", "Presentasi", dst.
            $table->string('description')->nullable();       // Keterangan singkat
            $table->string('icon')->nullable();              // heroicon name, e.g. heroicon-o-microphone
            $table->unsignedSmallInteger('sort_order')->default(0); // Urutan di dropdown
            $table->boolean('is_active')->default(true);    // Bisa dinonaktifkan tanpa hapus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selection_stage_types');
    }
};
