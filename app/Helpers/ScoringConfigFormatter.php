<?php

namespace App\Helpers;

use App\Models\Question;

class ScoringConfigFormatter
{
    /**
     * Format scoring config for display in views
     */
    public function formatScoringConfig(Question $question): string
    {
        if ($question->type === 'structural') {
            $score = 0;
            $options = $question->options;
            if (is_array($options)) {
                foreach ($options as $opt) {
                    if (isset($opt['score'])) {
                        $score = $opt['score'];
                        break;
                    }
                }
            }

            return '<strong>Bobot Nilai:</strong> ' . $score . ' Poin';
        }

        return '<strong>Tipe Teknis:</strong> Benar/Salah';
    }

    /**
     * Static convenience helper
     */
    public static function format(Question $question): string
    {
        return (new self())->formatScoringConfig($question);
    }
}
