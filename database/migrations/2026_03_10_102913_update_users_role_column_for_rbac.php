<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom role dari ENUM('admin','user') ke VARCHAR
     * agar mendukung role baru: super_admin, admin, observer, user.
     */
    public function up(): void
    {
        // MySQL: ubah enum ke varchar(50) agar fleksibel
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'user'");
    }

    /**
     * Kembalikan ke enum semula.
     */
    public function down(): void
    {
        // Bersihkan dulu role yang tidak termasuk enum lama
        DB::table('users')
            ->whereNotIn('role', ['admin', 'user'])
            ->update(['role' => 'user']);

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }
};
