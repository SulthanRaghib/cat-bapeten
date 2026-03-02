<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Question;
use App\Models\QuestionSubUnit;
use App\Models\QuestionUnit;
use App\Models\QuestionUnitIndicator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resolve exam types by code (seeded via migration)
        $tekType = ExamType::where('code', 'TEK')->first();
        $strType = ExamType::where('code', 'MAN')->first();

        if (! $tekType || ! $strType) {
            $this->command->warn('ExamType TEK/MAN not found. Run migrations first.');
            return;
        }

        // =================================================================
        // MASTER DATA: Units & Sub-Units
        // =================================================================

        // --- Teknis units & sub-units ---
        $tekUnitsMap = [
            'Proteksi Radiasi' => [
                'Prinsip Dasar Proteksi Radiasi',
                'Dosis dan Satuan Radiasi',
                'Perisai Radiasi',
            ],
            'Fisika Radiasi' => [
                'Struktur Atom & Peluruhan Radioaktif',
                'Interaksi Radiasi dengan Materi',
                'Spektrometri & Deteksi Radiasi',
            ],
            'Keselamatan Reaktor Nuklir' => [
                'Desain & Sistem Keselamatan Reaktor',
                'Analisis Kecelakaan Dasar Desain',
                'Pendinginan Darurat & Penanggulangan',
            ],
            'Pengawasan Tenaga Nuklir' => [
                'Perizinan Instalasi Nuklir',
                'Inspeksi & Penegakan Hukum',
                'Standar & Regulasi BAPETEN',
            ],
        ];

        // --- Mansoskul units & sub-units ---
        $manUnitsMap = [
            'Integritas' => [
                'Konsistensi Tindakan & Ucapan',
                'Kejujuran dalam Pelaporan',
            ],
            'Kerjasama' => [
                'Kolaborasi Tim',
                'Penyelesaian Konflik',
            ],
            'Komunikasi' => [
                'Komunikasi Lisan',
                'Komunikasi Tertulis',
            ],
            'Orientasi pada Hasil' => [
                'Pencapaian Target',
                'Efisiensi Kerja',
            ],
            'Pelayanan Publik' => [
                'Responsivitas Layanan',
                'Etika Pelayanan',
            ],
        ];

        // --- Default NAB indicators for Mansoskul units ---
        $manIndicatorTemplate = [
            ['name' => 'Di Bawah Standar',       'min_score' => 0,  'max_score' => 5,  'is_passing' => false, 'sort_order' => 1],
            ['name' => 'Memenuhi Sebagian',       'min_score' => 6,  'max_score' => 12, 'is_passing' => false, 'sort_order' => 2],
            ['name' => 'Memenuhi Standar',        'min_score' => 13, 'max_score' => 18, 'is_passing' => true,  'sort_order' => 3],
            ['name' => 'Melebihi Standar',        'min_score' => 19, 'max_score' => 25, 'is_passing' => true,  'sort_order' => 4],
            ['name' => 'Jauh Melebihi Standar',   'min_score' => 26, 'max_score' => 50, 'is_passing' => true,  'sort_order' => 5],
        ];

        // Helper: create units + sub-units, return nested id map
        $createUnits = function (int $examTypeId, array $unitsMap): array {
            $result = []; // ['Unit Name' => ['id' => x, 'subs' => ['Sub Name' => id, ...]]]

            foreach ($unitsMap as $unitName => $subNames) {
                $unit = QuestionUnit::firstOrCreate(
                    ['exam_type_id' => $examTypeId, 'name' => $unitName],
                    ['is_active' => true],
                );

                $subs = [];
                foreach ($subNames as $subName) {
                    $sub = QuestionSubUnit::firstOrCreate(
                        ['question_unit_id' => $unit->id, 'name' => $subName],
                    );
                    $subs[$subName] = $sub->id;
                }

                $result[$unitName] = ['id' => $unit->id, 'subs' => $subs];
            }

            return $result;
        };

        $tekUnits = $createUnits($tekType->id, $tekUnitsMap);
        $manUnits = $createUnits($strType->id, $manUnitsMap);

        // --- Seed default indicators for each Mansoskul unit ---
        foreach ($manUnits as $unitData) {
            foreach ($manIndicatorTemplate as $tpl) {
                QuestionUnitIndicator::firstOrCreate(
                    [
                        'question_unit_id' => $unitData['id'],
                        'name'             => $tpl['name'],
                    ],
                    [
                        'min_score'  => $tpl['min_score'],
                        'max_score'  => $tpl['max_score'],
                        'is_passing' => $tpl['is_passing'],
                        'sort_order' => $tpl['sort_order'],
                    ],
                );
            }
        }

        $this->command->info('✅ QuestionUnits, SubUnits & Indicators seeded.');

        // =================================================================
        // QUESTIONS
        // =================================================================

        // Pre-compute flat arrays for random picking
        $tekUnitKeys  = array_keys($tekUnits);
        $manUnitKeys  = array_keys($manUnits);

        // ---------------------------------------------------------
        // 1. Generate 100 Soal TEKNIKAL
        // ---------------------------------------------------------
        $technicalQuestions = [];

        for ($i = 1; $i <= 100; $i++) {
            $difficulty = Arr::random(['easy', 'medium', 'hard']);

            // Pick a random unit & sub-unit
            $unitName  = Arr::random($tekUnitKeys);
            $unitId    = $tekUnits[$unitName]['id'];
            $subNames  = array_keys($tekUnits[$unitName]['subs']);
            $subName   = Arr::random($subNames);
            $subUnitId = $tekUnits[$unitName]['subs'][$subName];

            // Random correct answer index (0-4)
            $correctIndex = rand(0, 4);
            $labels       = ['A', 'B', 'C', 'D', 'E'];
            $options      = [];

            foreach ($labels as $index => $label) {
                $isCorrect = ($index === $correctIndex);
                $options[] = [
                    'answer_text' => "<p>Pilihan $label untuk soal teknis $i ($difficulty) - " . ($isCorrect ? 'Benar' : 'Salah') . '</p>',
                    'is_correct'  => $isCorrect,
                    'is_active'   => true,
                ];
            }

            $technicalQuestions[] = [
                'exam_type_id'         => $tekType->id,
                'question_unit_id'     => $unitId,
                'question_sub_unit_id' => $subUnitId,
                'category'             => $difficulty,
                'question_text'        => "<p><strong>[Teknis — {$unitName} / {$subName}]</strong> ({$difficulty}) Contoh pertanyaan dummy nomor $i. Pilihlah jawaban yang benar.</p>",
                'options'              => json_encode($options),
                'scoring_config'       => '[]',
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        foreach (array_chunk($technicalQuestions, 50) as $chunk) {
            Question::insert($chunk);
        }

        $this->command->info('✅ 100 soal Teknis seeded (with units & sub-units).');

        // ---------------------------------------------------------
        // 2. Generate 100 Soal MANSOSKUL  (weighted / skala Likert)
        // ---------------------------------------------------------
        $structuralQuestions = [];

        $choices = [
            ['text' => 'Sangat Setuju',        'val' => 5],
            ['text' => 'Setuju',               'val' => 4],
            ['text' => 'Ragu-ragu',            'val' => 3],
            ['text' => 'Tidak Setuju',         'val' => 2],
            ['text' => 'Sangat Tidak Setuju',  'val' => 1],
        ];

        for ($i = 1; $i <= 100; $i++) {
            // Pick a random unit & sub-unit
            $unitName  = Arr::random($manUnitKeys);
            $unitId    = $manUnits[$unitName]['id'];
            $subNames  = array_keys($manUnits[$unitName]['subs']);
            $subName   = Arr::random($subNames);
            $subUnitId = $manUnits[$unitName]['subs'][$subName];

            $options = [];
            foreach ($choices as $choice) {
                $options[] = [
                    'answer_text' => "<p>{$choice['text']} (Soal Mansoskul $i)</p>",
                    'score'       => $choice['val'],
                    'is_active'   => true,
                ];
            }

            $structuralQuestions[] = [
                'exam_type_id'         => $strType->id,
                'question_unit_id'     => $unitId,
                'question_sub_unit_id' => $subUnitId,
                'category'             => null,
                'question_text'        => "<p><strong>[Mansoskul — {$unitName} / {$subName}]</strong> Studi kasus nomor $i. Bagaimana sikap Anda dalam situasi ini?</p>",
                'options'              => json_encode($options),
                'scoring_config'       => '[]',
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        foreach (array_chunk($structuralQuestions, 50) as $chunk) {
            Question::insert($chunk);
        }

        $this->command->info('✅ 100 soal Mansoskul seeded (with units, sub-units & NAB indicators).');
    }
}
