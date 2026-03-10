<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldRoleSeeder extends Seeder
{
    /**
     * Seed 3 roles: super_admin, admin, observer.
     *
     * - super_admin: bypass semua permission via Gate::before (Shield config)
     * - admin: semua permission kecuali Role dan Shield management
     * - observer: hanya boleh lihat Monitoring Ujian
     *
     * PENTING: seeder ini menjalankan shield:generate terlebih dahulu
     * agar permissions sudah tersedia bahkan saat migrate:fresh --seed.
     */
    public function run(): void
    {
        // ================================================================
        // 0. Generate semua permissions dari resource yang ada.
        //    Memastikan permissions tersedia bahkan saat migrate:fresh --seed
        // ================================================================
        $this->command->info('Generating Shield permissions...');
        Artisan::call('shield:generate', [
            '--all'                      => true,
            '--panel'                    => 'admin',
            '--option'                   => 'policies_and_permissions',
            '--ignore-existing-policies' => true,
        ]);
        $this->command->line(Artisan::output());

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ================================================================
        // 1. SUPER ADMIN — assign ALL permissions agar Shield UI menampilkan
        //    semua permission ter-centang. Gate::before tetap berlaku sebagai bypass.
        // ================================================================
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
        );

        $superAdmin->syncPermissions(Permission::where('guard_name', 'web')->pluck('name'));

        // ================================================================
        // 2. ADMIN — Semua permission kecuali Role & Shield management
        //    (hanya super_admin yang boleh kelola role & permission)
        // ================================================================
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
        );

        $adminPermissions = Permission::where('guard_name', 'web')
            ->where('name', 'not like', '%Role%')   // Kecuali role management
            ->where('name', 'not like', '%shield%') // Kecuali Shield resource
            ->pluck('name');

        $admin->syncPermissions($adminPermissions);

        // ================================================================
        // 3. OBSERVER — Pengawas ujian, HANYA boleh lihat Monitoring Ujian.
        //    ExamMonitorResource → ExamSession  → ViewAny:ExamSession
        //    ExamResultResource  → ExamResult   → ViewAny:ExamResult  (model terpisah!)
        //    Observer hanya dapat ExamSession perms → Hasil Ujian tidak muncul.
        // ================================================================
        $observer = Role::firstOrCreate(
            ['name' => 'observer', 'guard_name' => 'web'],
        );

        $observerPermissionNames = [
            'ViewAny:ExamSession',
            'View:ExamSession',
        ];

        $observer->syncPermissions($observerPermissionNames);

        // ================================================================
        // 4. ASSIGN ROLE ke akun yang sudah di-seed oleh UserSeeder
        //    withoutEvents agar tidak trigger booted() listener di User model
        // ================================================================
        $roleAssignments = [
            'superadmin@bapeten.com' => 'super_admin',
            'admin@bapeten.com'      => 'admin',
            'observer@bapeten.com'   => 'observer',
        ];

        foreach ($roleAssignments as $email => $roleName) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->syncRoles([$roleName]);
                $user->updateQuietly(['role' => $roleName]);
                $this->command->info("  Assigned '{$roleName}' → {$email}");
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('');
        $this->command->info('Shield roles seeded:');
        $this->command->info('  super_admin : ' . $superAdmin->fresh()->permissions->count() . ' permissions (all)');
        $this->command->info('  admin       : ' . $admin->fresh()->permissions->count() . ' permissions');
        $this->command->info('  observer    : ' . $observer->fresh()->permissions->count() . ' permissions (Monitoring Ujian only)');
    }
}
