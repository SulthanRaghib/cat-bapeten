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
                foreach ($options as $idx => $opt) {
                    $label = chr(65 + $idx);
                    $score = null;
                    if (is_array($opt) && isset($opt['score'])) {
                        $score = $opt['score'];
                    } elseif (isset($question->scoring_config[$idx])) {
                        $score = $question->scoring_config[$idx];
                    }
                    $score = $score !== null ? $score : 0;
                    $html .= "<li>{$label}: {$score} poin</li>";
                }
            }
            $html .= '</ul>';

            return $html;
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
