<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Question;
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

        // ---------------------------------------------------------
        // 1. Generate 100 Soal TEKNIKAL
        // Format Option: [{"answer_text":"...","is_correct":true,"is_active":true}, ...]
        // ---------------------------------------------------------
        $technicalQuestions = [];

        for ($i = 1; $i <= 100; $i++) {
            $difficulty = Arr::random(['easy', 'medium', 'hard']);

            // Kita tentukan secara acak index mana yang benar (0 sampai 4)
            $correctIndex = rand(0, 4);

            $options = [];
            $labels = ['A', 'B', 'C', 'D', 'E'];

            foreach ($labels as $index => $label) {
                $isCorrect = ($index === $correctIndex);

                $options[] = [
                    'answer_text' => "<p>Pilihan $label untuk soal teknis $i ($difficulty) - " . ($isCorrect ? 'Benar' : 'Salah') . "</p>",
                    'is_correct' => $isCorrect,
                    'is_active' => true,
                ];
            }

            $technicalQuestions[] = [
                'exam_type_id' => $tekType->id,
                'category' => $difficulty,
                'question_text' => "<p><strong>[Teknis - {$difficulty}]</strong> Ini adalah contoh pertanyaan dummy nomor $i. Pilihlah jawaban yang benar.</p>",
                'options' => json_encode($options),
                'scoring_config' => '[]', // Scoring config dikosongkan sesuai request
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert batch Technical
        foreach (array_chunk($technicalQuestions, 50) as $chunk) {
            Question::insert($chunk);
        }

        // ---------------------------------------------------------
        // 2. Generate 100 Soal MANSOSKUL
        // Format Option: [{"answer_text":"...","score":5,"is_active":true}, ...]
        // ---------------------------------------------------------
        $structuralQuestions = [];

        for ($i = 1; $i <= 100; $i++) {
            $options = [];

            // Logika Bobot: A=5, B=4, C=3, D=2, E=1
            $choices = [
                ['text' => "Sangat Setuju", 'val' => 5],
                ['text' => "Setuju", 'val' => 4],
                ['text' => "Ragu-ragu", 'val' => 3],
                ['text' => "Tidak Setuju", 'val' => 2],
                ['text' => "Sangat Tidak Setuju", 'val' => 1],
            ];

            foreach ($choices as $choice) {
                $options[] = [
                    'answer_text' => "<p>{$choice['text']} (Soal Mansoskul $i)</p>",
                    'score' => $choice['val'],
                    'is_active' => true,
                ];
            }

            $structuralQuestions[] = [
                'exam_type_id' => $strType->id,
                'category' => null, // Mansoskul biasanya tidak punya kategori difficulty
                'question_text' => "<p><strong>[Mansoskul]</strong> Ini adalah studi kasus nomor $i. Bagaimana sikap Anda dalam situasi ini?</p>",
                'options' => json_encode($options),
                'scoring_config' => '[]', // Scoring config dikosongkan sesuai request
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert batch Mansoskul
        foreach (array_chunk($structuralQuestions, 50) as $chunk) {
            Question::insert($chunk);
        }
    }
}
