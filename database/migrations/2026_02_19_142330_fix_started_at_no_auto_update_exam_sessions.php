<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove ON UPDATE CURRENT_TIMESTAMP from started_at.
     *
     * MySQL automatically assigns ON UPDATE CURRENT_TIMESTAMP to the first
     * TIMESTAMP column that has no explicit DEFAULT/ON UPDATE clause, causing
     * started_at to be overwritten with the current time on every UPDATE.
     */
    public function up(): void
    {
        // Explicitly declare DEFAULT CURRENT_TIMESTAMP without ON UPDATE
        DB::statement('ALTER TABLE exam_sessions MODIFY started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        // Restore original (auto-update) behaviour if needed
        DB::statement('ALTER TABLE exam_sessions MODIFY started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
