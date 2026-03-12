<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class AppearanceSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.appearance-settings';

    public static function getNavigationLabel(): string
    {
        return __('Appearance');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Appearance Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public string $currentColor = 'yellow';

    public function mount(): void
    {
        $this->currentColor = auth()->user()->theme_color ?? 'yellow';
    }

    public function setColor(string $color): void
    {
        $allowed = ['yellow', 'orange', 'red', 'pink', 'purple', 'indigo', 'sky', 'cyan', 'teal', 'green', 'lime'];
        if (! in_array($color, $allowed, true)) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->update(['theme_color' => $color]);

        $this->currentColor = $color;

        Notification::make()
            ->title(__('Theme changed successfully!'))
            ->success()
            ->duration(2000)
            ->send();

        $this->redirect(static::getUrl());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canAccessPanel(
            \Filament\Facades\Filament::getCurrentPanel()
        );
    }
}
