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
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('finished_at');
            $table->integer('total_paused_time')->default(0)->after('paused_at')->comment('Total paused duration in seconds');

            // Modify enum is tricky in some drivers, but for MySQL/MariaDB we can just alter
            // Note: DB::statement might be needed for enum modification if using strict mode or older laravel
            // In standard Laravel migration with modifying column:
            $table->enum('status', ['ongoing', 'paused', 'completed', 'terminated'])->default('ongoing')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'total_paused_time']);
            // Revert enum is hard without raw SQL, usually skipped in down if risk is low
            $table->enum('status', ['ongoing', 'completed', 'terminated'])->default('ongoing')->change();
        });
    }
};
