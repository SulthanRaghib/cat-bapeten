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
        Schema::table('questions', function (Blueprint $table) {
            $table->longText('explanation')->nullable()->after('options');
            // Technical fields
            $table->string('unit')->nullable()->after('explanation');
            $table->string('sub_unit')->nullable()->after('unit');
            $table->string('category')->nullable()->after('sub_unit'); // easy, medium, hard
            // Structural fields
            $table->string('competence_area')->nullable()->after('category');
            $table->string('competence_sub_area')->nullable()->after('competence_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'explanation',
                'unit',
                'sub_unit',
                'category',
                'competence_area',
                'competence_sub_area'
            ]);
        });
    }
};
