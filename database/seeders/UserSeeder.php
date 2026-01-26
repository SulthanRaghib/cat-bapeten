<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@bapeten.com'],
            [
                'name' => 'Administrator BAPETEN',
                'nip' => '198001012024011001',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info("Super Admin seeded successfully: {$superAdmin->email}");

        $faker = FakerFactory::create();
        $roles = ['user'];

        for ($i = 0; $i < 10; $i++) {
            User::factory()->create([
                'nip' => $faker->unique()->numerify(str_repeat('#', 18)),
                'role' => $faker->randomElement($roles),
            ]);
        }

        $this->command->info('Random users seeded successfully.');
    }
}
