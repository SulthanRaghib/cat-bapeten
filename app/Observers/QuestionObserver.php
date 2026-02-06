<?php

namespace App\Observers;

use App\Models\Question;

class QuestionObserver
{
    /**
     * Handle the Question "saving" event.
     * Normalize scoring_config from options if empty.
     */
    public function saving(Question $question): void
    {
        $current = $question->scoring_config ?? [];
        $isEmpty = empty($current) || (is_array($current) && count($current) === 0);

        if ($isEmpty) {
            $question->scoring_config = $question->generateScoringConfigFromOptions();
        }
    }
}
