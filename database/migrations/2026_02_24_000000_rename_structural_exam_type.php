<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update ExamType name and code from Struktural/STR to Mansoskul/MAN
        DB::table('exam_types')
            ->where('code', 'STR')
            ->update([
                'name' => 'Mansoskul',
                'code' => 'MAN',
            ]);

        // Also update name if it was just 'Struktural' but code was somehow different
        // or to catch cases where code might have been manually changed but name wasn't.
        DB::table('exam_types')
            ->where('name', 'Struktural')
            ->where('code', '!=', 'MAN') // avoid double update if possible, though update is idempotent usually
            ->update([
                'name' => 'Mansoskul',
                'code' => 'MAN',
            ]);
    }

    public function down(): void
    {
        // Revert Mansoskul/MAN to Struktural/STR
        DB::table('exam_types')
            ->where('code', 'MAN')
            ->update([
                'name' => 'Struktural',
                'code' => 'STR',
            ]);
    }
};
