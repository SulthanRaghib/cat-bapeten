<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expand exam_sessions.status ENUM to include 'awaiting_interview'.
 * Using raw SQL because Blueprint::change() on ENUM columns is unreliable
 * across MySQL/MariaDB versions.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE exam_sessions
            MODIFY COLUMN status
                ENUM('ongoing','paused','completed','terminated','awaiting_interview')
                NOT NULL DEFAULT 'ongoing'
        ");
    }

    public function down(): void
    {
        // Revert: rows with 'awaiting_interview' will get the default ('ongoing') on rollback.
        DB::statement("
            UPDATE exam_sessions SET status = 'completed' WHERE status = 'awaiting_interview'
        ");

        DB::statement("
            ALTER TABLE exam_sessions
            MODIFY COLUMN status
                ENUM('ongoing','paused','completed','terminated')
                NOT NULL DEFAULT 'ongoing'
        ");
    }
};
