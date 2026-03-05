<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SelectionStageType;
use Illuminate\Database\Seeder;

class SelectionStageTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Wawancara',    'description' => 'Seleksi melalui wawancara langsung dengan interviewer.',     'icon' => 'heroicon-o-microphone',              'sort_order' => 1],
            ['name' => 'Presentasi',   'description' => 'Peserta mempresentasikan topik atau studi kasus tertentu.',  'icon' => 'heroicon-o-presentation-chart-bar',  'sort_order' => 2],
            ['name' => 'FGD',          'description' => 'Focus Group Discussion — diskusi kelompok terstruktur.',     'icon' => 'heroicon-o-user-group',              'sort_order' => 3],
            ['name' => 'Psikotes',     'description' => 'Tes psikologi untuk menilai kepribadian dan kemampuan.',     'icon' => 'heroicon-o-academic-cap',            'sort_order' => 4],
            ['name' => 'Praktik',      'description' => 'Uji kompetensi berbasis praktik/demonstrasi langsung.',      'icon' => 'heroicon-o-wrench-screwdriver',       'sort_order' => 5],
            ['name' => 'Tes Fisik',    'description' => 'Penilaian kondisi dan kemampuan fisik peserta.',             'icon' => 'heroicon-o-bolt',                    'sort_order' => 6],
        ];

        foreach ($types as $type) {
            SelectionStageType::firstOrCreate(
                ['name' => $type['name']],
                array_merge($type, ['is_active' => true]),
            );
        }
    }
}
