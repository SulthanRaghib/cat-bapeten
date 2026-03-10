<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    /**
     * Seed akun untuk setiap role:
     * - 1 Super Admin  → superadmin@bapeten.com
     * - 1 Admin        → admin@bapeten.com
     * - 1 Observer     → observer@bapeten.com
     * - 5 Peserta Ujian (dummy)
     *
     * Spatie role & DB role column akan di-sync oleh ShieldRoleSeeder
     * yang berjalan setelah seeder ini.
     */
    public function run(): void
    {
        // ================================================================
        // 1. SUPER ADMIN
        // ================================================================
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@bapeten.com'],
            [
                'name'              => 'Super Admin BAPETEN',
                'nip'               => '198001012024011001',
                'role'              => 'super_admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $this->command->info("Super Admin : {$superAdmin->email}");

        // ================================================================
        // 2. ADMIN (Administrator)
        // ================================================================
        $admin = User::updateOrCreate(
            ['email' => 'admin@bapeten.com'],
            [
                'name'              => 'Administrator BAPETEN',
                'nip'               => '198501152024011002',
                'role'              => 'admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $this->command->info("Admin       : {$admin->email}");

        // ================================================================
        // 3. OBSERVER (Pengawas Ujian)
        // ================================================================
        $observer = User::updateOrCreate(
            ['email' => 'observer@bapeten.com'],
            [
                'name'              => 'Pengawas Ujian BAPETEN',
                'nip'               => '199003202024011003',
                'role'              => 'observer',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $this->command->info("Observer    : {$observer->email}");

        // ================================================================
        // 4. DUMMY PESERTA UJIAN (role = user)
        // ================================================================
        $faker = FakerFactory::create('id_ID');

        for ($i = 0; $i < 5; $i++) {
            User::factory()->create([
                'nip'  => $faker->unique()->numerify(str_repeat('#', 18)),
                'role' => 'user',
            ]);
        }

        $this->command->info('5 dummy peserta ujian seeded.');
    }
}
