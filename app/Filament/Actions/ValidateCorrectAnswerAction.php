<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\ExamType;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

/**
 * Reusable validation action: ensures a Teknis/correct_wrong question
 * has at least one option marked as the correct answer.
 *
 * Extracted from CreateQuestion and EditQuestion to satisfy DRY —
 * the identical guard logic previously lived in both pages.
 *
 * Usage (in CreateRecord or EditRecord page):
 *
 *   protected function afterValidate(): void
 *   {
 *       ValidateCorrectAnswerAction::run($this);
 *   }
 */
final class ValidateCorrectAnswerAction
{
    /**
     * @param  CreateRecord|EditRecord  $page  The calling Filament page.
     *
     * Calls $page->halt() when the validation fails, which prevents the
     * record from being saved without scrolling the user away from the form.
     */
    public static function run(CreateRecord|EditRecord $page): void
    {
        $data       = $page->form->getState();
        $examTypeId = $data['exam_type_id'] ?? null;
        $method     = $examTypeId
            ? ExamType::find($examTypeId)?->evaluation_method
            : null;

        // Only enforce for correct_wrong (Teknis) type.
        if ($method !== 'correct_wrong') {
            return;
        }

        $hasCorrect = collect($data['options'] ?? [])
            ->contains(fn(mixed $opt): bool => is_array($opt) && ! empty($opt['is_correct']));

        if ($hasCorrect) {
            return;
        }

        Notification::make()
            ->title('Kunci Jawaban Belum Dipilih')
            ->body('Soal tipe Teknis wajib memiliki minimal 1 jawaban yang ditandai sebagai Kunci Jawaban (Benar).')
            ->danger()
            ->persistent()
            ->send();

        $page->halt();
    }
}
