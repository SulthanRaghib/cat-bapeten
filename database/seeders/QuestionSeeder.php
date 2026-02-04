<?php

namespace Database\Seeders;

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
                'type' => 'technical',
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
        // 2. Generate 100 Soal STRUKTURAL
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
                    'answer_text' => "<p>{$choice['text']} (Soal Struktural $i)</p>",
                    'score' => $choice['val'],
                    'is_active' => true,
                ];
            }

            $structuralQuestions[] = [
                'type' => 'structural',
                'category' => null, // Struktural biasanya tidak punya kategori difficulty
                'question_text' => "<p><strong>[Struktural]</strong> Ini adalah studi kasus nomor $i. Bagaimana sikap Anda dalam situasi ini?</p>",
                'options' => json_encode($options),
                'scoring_config' => '[]', // Scoring config dikosongkan sesuai request
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert batch Structural
        foreach (array_chunk($structuralQuestions, 50) as $chunk) {
            Question::insert($chunk);
        }
    }
}
