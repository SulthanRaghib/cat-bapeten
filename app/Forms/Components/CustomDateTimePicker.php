<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class CustomDateTimePicker extends Field
{
    protected string $view = 'forms.components.custom-date-time-picker';

    protected function setUp(): void
    {
        parent::setUp();

        // Normalize empty string → null so Filament/DB treats it as NULL
        $this->dehydrateStateUsing(fn($state): ?string => $state ?: null);
    }

    /** Keep API compatible with Filament's DateTimePicker — display/timezone/seconds are handled in Alpine */
    public function displayFormat(string $format): static
    {
        return $this;
    }
    public function timezone(string $timezone): static
    {
        return $this;
    }
    public function seconds(bool $condition = true): static
    {
        return $this;
    }
    public function native(bool $condition = true): static
    {
        return $this;
    }
}
