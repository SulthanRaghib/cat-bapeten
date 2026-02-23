<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Question\CreateQuestionDTO;
use App\DTOs\Question\UpdateQuestionDTO;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

/**
 * QuestionService — sole owner of all question business logic.
 *
 * Responsibilities:
 *  - Persist question + nested option array in a single atomic transaction.
 *  - Auto-generate scoring_config from the typed option data.
 *  - Keep the delivery layer (Filament Pages) ignorant of the "how".
 */
final class QuestionService
{
    // ──────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create a new question and derive its scoring configuration.
     *
     * @throws \Throwable  Re-throws any DB / validation exception.
     */
    public function create(CreateQuestionDTO $dto): Question
    {
        return DB::transaction(function () use ($dto): Question {
            $question = Question::create($this->mapToAttributes($dto));

            // Derive scoring_config NOW that the model is persisted and has
            // all options stored, so scoring rules are always in sync.
            $question->scoring_config = $question->generateScoringConfigFromOptions();
            $question->saveQuietly(); // avoid firing another create event

            return $question;
        });
    }

    /**
     * Update an existing question and re-derive its scoring configuration.
     *
     * @throws \Throwable
     */
    public function update(Question $question, UpdateQuestionDTO $dto): Question
    {
        return DB::transaction(function () use ($question, $dto): Question {
            $question->fill($this->mapToAttributes($dto));

            // Re-derive scoring_config whenever options change —
            // ensures score calculation never drifts out of sync.
            $question->scoring_config = $question->generateScoringConfigFromOptions();
            $question->save();

            return $question->fresh(); // return a clean, reloaded instance
        });
    }

    /**
     * Permanently delete a question.
     * Pivot records (exam_package_question) are cleaned up by DB constraints.
     *
     * @throws \Throwable
     */
    public function delete(Question $question): void
    {
        DB::transaction(static function () use ($question): void {
            $question->delete();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Convert a Create / Update DTO into a plain attribute array the model
     * accepts.  Typed options are serialized back to the JSON-castable format.
     *
     * @param  CreateQuestionDTO|UpdateQuestionDTO  $dto
     * @return array<string, mixed>
     */
    private function mapToAttributes(CreateQuestionDTO|UpdateQuestionDTO $dto): array
    {
        return [
            'type'                 => $dto->type,
            'question_text'        => $dto->questionText,
            'explanation'          => $dto->explanation,
            'unit'                 => $dto->unit,
            'sub_unit'             => $dto->subUnit,
            'category'             => $dto->category,
            'competence_area'      => $dto->competenceArea,
            'competence_sub_area'  => $dto->competenceSubArea,

            // Serialize the typed value objects back to the array format the
            // Question model casts as JSON ("options" column).
            'options' => array_map(
                static fn($opt) => $opt->toArray(),
                $dto->options,
            ),
        ];
    }
}
