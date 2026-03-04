<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\DTOs\Question\CreateQuestionDTO;
use App\Models\ExamType;
use App\Models\QuestionSubUnit;
use App\Models\QuestionUnit;
use App\Models\QuestionUnitIndicator;
use App\Services\QuestionService;
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
        $service = app(QuestionService::class);

        // ---------------------------------------------------------
        // 1. Generate 100 Soal TEKNIKAL
        //    Menggunakan QuestionService::create() — alur identik dengan
        //    pembuatan soal manual via form admin.
        //    - options: is_correct toggle, score field tersembunyi (0 dari form)
        //    - QuestionOptionData::toArray() otomatis set score=1 jika is_correct=true
        //    - generateScoringConfigFromOptions() otomatis build scoring_config
        // ---------------------------------------------------------
        for ($i = 1; $i <= 100; $i++) {
            $difficulty = Arr::random(['easy', 'medium', 'hard']);

            $unitName  = Arr::random($tekUnitKeys);
            $unitId    = $tekUnits[$unitName]['id'];
            $subNames  = array_keys($tekUnits[$unitName]['subs']);
            $subName   = Arr::random($subNames);
            $subUnitId = $tekUnits[$unitName]['subs'][$subName];

            $correctIndex = rand(0, 4);
            $labels       = ['A', 'B', 'C', 'D', 'E'];

            // Simulasikan data form Teknis:
            // - score tidak ada di form (field hidden → default 0)
            // - is_correct diisi via toggle
            $optionsFormData = [];
            foreach ($labels as $idx => $label) {
                $isCorrect         = ($idx === $correctIndex);
                $optionsFormData[] = [
                    'answer_text' => "<p>Pilihan $label untuk soal teknis $i ($difficulty) - " . ($isCorrect ? 'Benar' : 'Salah') . '</p>',
                    'is_correct'  => $isCorrect,
                    // score tidak diisi di form untuk teknis (seperti form asli)
                    'is_active'   => true,
                ];
            }

            $service->create(CreateQuestionDTO::fromFormData([
                'exam_type_id'         => $tekType->id,
                'question_unit_id'     => $unitId,
                'question_sub_unit_id' => $subUnitId,
                'category'             => $difficulty,
                'question_text'        => "<p><strong>[Teknis — {$unitName} / {$subName}]</strong> ({$difficulty}) Contoh pertanyaan dummy nomor $i. Pilihlah jawaban yang benar.</p>",
                'explanation'          => '',
                'options'              => $optionsFormData,
            ]));
        }

        $this->command->info('✅ 100 soal Teknis seeded (via QuestionService).');

        // ---------------------------------------------------------
        // 2. Generate 100 Soal MANSOSKUL  (weighted / skala Likert)
        //    - score diisi eksplisit per opsi (5,4,3,2,1)
        //    - is_correct selalu false (tidak ada jawaban benar tunggal)
        //    - generateScoringConfigFromOptions() akan build scoring_config tanpa key 'correct'
        // ---------------------------------------------------------
        $choices = [
            ['text' => 'Sangat Setuju',       'val' => 5],
            ['text' => 'Setuju',              'val' => 4],
            ['text' => 'Ragu-ragu',           'val' => 3],
            ['text' => 'Tidak Setuju',        'val' => 2],
            ['text' => 'Sangat Tidak Setuju', 'val' => 1],
        ];

        for ($i = 1; $i <= 100; $i++) {
            $unitName  = Arr::random($manUnitKeys);
            $unitId    = $manUnits[$unitName]['id'];
            $subNames  = array_keys($manUnits[$unitName]['subs']);
            $subName   = Arr::random($subNames);
            $subUnitId = $manUnits[$unitName]['subs'][$subName];

            // Simulasikan data form Mansoskul:
            // - score diisi via TextInput (visible untuk tipe weighted)
            // - is_correct selalu false
            $optionsFormData = [];
            foreach ($choices as $choice) {
                $optionsFormData[] = [
                    'answer_text' => "<p>{$choice['text']} (Soal Mansoskul $i)</p>",
                    'is_correct'  => false,
                    'score'       => $choice['val'],
                    'is_active'   => true,
                ];
            }

            $service->create(CreateQuestionDTO::fromFormData([
                'exam_type_id'         => $strType->id,
                'question_unit_id'     => $unitId,
                'question_sub_unit_id' => $subUnitId,
                'question_text'        => "<p><strong>[Mansoskul — {$unitName} / {$subName}]</strong> Studi kasus nomor $i. Bagaimana sikap Anda dalam situasi ini?</p>",
                'explanation'          => '',
                'options'              => $optionsFormData,
            ]));
        }

        $this->command->info('✅ 100 soal Mansoskul seeded (via QuestionService).');
    }
}
