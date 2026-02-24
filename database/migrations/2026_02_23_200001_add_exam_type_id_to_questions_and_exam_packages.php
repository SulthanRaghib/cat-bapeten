<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the hardcoded `type` string column on `questions` and `exam_packages`
 * with a foreign key to the new `exam_types` master-data table.
 *
 * This migration is SAFE to run on existing data — it seeds two default
 * ExamType rows (TEK / STR) and maps every existing record before dropping
 * the legacy column.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────────────────────────
        // 1. Seed default exam types so FK mapping never hits a null target.
        // ──────────────────────────────────────────────────────────────────
        $now = now();

        $tekId = DB::table('exam_types')->insertGetId([
            'name'              => 'Teknis',
            'code'              => 'TEK',
            'evaluation_method' => 'correct_wrong',
            'is_active'         => true,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $strId = DB::table('exam_types')->insertGetId([
            'name'              => 'Mansoskul',
            'code'              => 'MAN',
            'evaluation_method' => 'weighted',
            'is_active'         => true,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // ──────────────────────────────────────────────────────────────────
        // 2. questions: add exam_type_id → map → drop type
        // ──────────────────────────────────────────────────────────────────
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignId('exam_type_id')
                ->nullable()          // temporary — will be made NOT NULL after data migration
                ->after('id')
                ->constrained('exam_types')
                ->cascadeOnDelete();
        });

        // Map existing rows
        DB::table('questions')->where('type', 'technical')->update(['exam_type_id' => $tekId]);
        DB::table('questions')->where('type', 'structural')->update(['exam_type_id' => $strId]);

        // Fallback: any unmapped rows default to TEK
        DB::table('questions')->whereNull('exam_type_id')->update(['exam_type_id' => $tekId]);

        // Now make the column NOT NULL
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignId('exam_type_id')->nullable(false)->change();
        });

        // Drop legacy column
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropColumn('type');
        });

        // ──────────────────────────────────────────────────────────────────
        // 3. exam_packages: add exam_type_id → map → drop type
        // ──────────────────────────────────────────────────────────────────
        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->foreignId('exam_type_id')
                ->nullable()
                ->after('title')
                ->constrained('exam_types')
                ->cascadeOnDelete();
        });

        DB::table('exam_packages')->where('type', 'technical')->update(['exam_type_id' => $tekId]);
        DB::table('exam_packages')->where('type', 'structural')->update(['exam_type_id' => $strId]);
        DB::table('exam_packages')->whereNull('exam_type_id')->update(['exam_type_id' => $tekId]);

        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->foreignId('exam_type_id')->nullable(false)->change();
        });

        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        // ── Restore exam_packages.type ──────────────────────────────────
        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->string('type')->default('technical')->after('title');
        });

        // Map back
        $types = DB::table('exam_types')->pluck('evaluation_method', 'id');
        foreach ($types as $id => $method) {
            $legacyType = $method === 'weighted' ? 'structural' : 'technical';
            DB::table('exam_packages')
                ->where('exam_type_id', $id)
                ->update(['type' => $legacyType]);
        }

        Schema::table('exam_packages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('exam_type_id');
        });

        // ── Restore questions.type ──────────────────────────────────────
        Schema::table('questions', function (Blueprint $table): void {
            $table->string('type')->default('technical')->after('id');
        });

        $types = DB::table('exam_types')->pluck('evaluation_method', 'id');
        foreach ($types as $id => $method) {
            $legacyType = $method === 'weighted' ? 'structural' : 'technical';
            DB::table('questions')
                ->where('exam_type_id', $id)
                ->update(['type' => $legacyType]);
        }

        Schema::table('questions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('exam_type_id');
        });
    }
};
