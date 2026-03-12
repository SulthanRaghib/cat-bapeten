<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Livewire\Component;

class ColorThemeSwitcher extends Component
{
    public string $currentColor = 'yellow';

    public function mount(): void
    {
        $this->currentColor = auth()->user()?->theme_color ?? 'yellow';
    }

    public function setColor(string $color): void
    {
        if (! array_key_exists($color, self::colorMap())) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->update(['theme_color' => $color]);

        $this->currentColor = $color;

        $this->dispatch('theme-changed', palette: self::getOklchPalette($color));

        Notification::make()
            ->title('Tema berhasil diubah!')
            ->success()
            ->duration(2000)
            ->send();
    }

    public static function colorMap(): array
    {
        return [
            'yellow' => ['label' => 'Kuning',      'hex' => '#f59e0b', 'palette' => Color::Amber],
            'orange' => ['label' => 'Oranye',      'hex' => '#f97316', 'palette' => Color::Orange],
            'red'    => ['label' => 'Merah',       'hex' => '#f43f5e', 'palette' => Color::Rose],
            'pink'   => ['label' => 'Pink',        'hex' => '#ec4899', 'palette' => Color::Pink],
            'purple' => ['label' => 'Ungu',        'hex' => '#a855f7', 'palette' => Color::Purple],
            'indigo' => ['label' => 'Biru Tua',    'hex' => '#6366f1', 'palette' => Color::Indigo],
            'sky'    => ['label' => 'Biru Langit', 'hex' => '#0ea5e9', 'palette' => Color::Sky],
            'cyan'   => ['label' => 'Biru Muda',   'hex' => '#06b6d4', 'palette' => Color::Cyan],
            'teal'   => ['label' => 'Tosca',       'hex' => '#14b8a6', 'palette' => Color::Teal],
            'green'  => ['label' => 'Hijau',       'hex' => '#10b981', 'palette' => Color::Emerald],
            'lime'   => ['label' => 'Lime',        'hex' => '#84cc16', 'palette' => Color::Lime],
        ];
    }

    public static function getOklchPalette(string $color): array
    {
        $map = self::colorMap();
        $palette = $map[$color]['palette'] ?? Color::Amber;

        $result = [];
        foreach ($palette as $shade => $oklch) {
            $result[$shade] = $oklch;
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.color-theme-switcher', [
            'colors' => self::colorMap(),
        ]);
    }
}
