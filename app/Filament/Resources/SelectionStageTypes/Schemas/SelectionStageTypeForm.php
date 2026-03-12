<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SelectionStageTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Selection Stage Information'))
                ->description(__('This data will appear as options when configuring Technical exam packages.'))
                ->icon('heroicon-o-queue-list')
                ->schema([
                    TextInput::make('name')
                        ->label(__('Stage Name'))
                        ->validationAttribute('Nama Tahap')
                        ->placeholder(__('e.g. Interview, FGD, Presentation'))
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),

                    TextInput::make('description')
                        ->label(__('Description'))
                        ->placeholder(__('Brief description of this stage'))
                        ->maxLength(255),

                    // Pencarian Icon
                    TextInput::make('icon_search')
                        ->label(__('Search Icon'))
                        ->placeholder(__('Type icon name... (e.g. user, document, building)'))
                        ->prefixIcon('heroicon-o-magnifying-glass')
                        ->live(debounce: 500)
                        ->dehydrated(false) // Field ini tidak disimpan ke database
                        ->helperText(__('Type to display icon options below.'))
                        ->hidden(true),


                    // Pilihan Icon Visual
                    ToggleButtons::make('icon')
                        ->label(__('Select Icon (Visual)'))
                        ->inline()
                        ->options(function (Get $get, ?string $state): array {
                            $search = (string) $get('icon_search');
                            return self::getIconOptions($search, $state);
                        })
                        ->icons(function (Get $get, ?string $state): array {
                            $search = (string) $get('icon_search');
                            $options = self::getIconOptions($search, $state);
                            // Map each option key to itself as the icon name
                            return array_combine(array_keys($options), array_keys($options));
                        })
                        ->helperText(__('Select the icon that best represents this stage.')),

                    TextInput::make('sort_order')
                        ->label(__('Display Order'))
                        ->validationAttribute('Urutan Tampil')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText(__('Smaller number appears first in dropdown.')),

                    Toggle::make('is_active')
                        ->label(__('Active'))
                        ->helperText(__('Inactive stage types will not appear in the exam package configuration dropdown.'))
                        ->default(true),
                ]),
        ]);
    }

    protected static function getIconOptions(string $search = '', ?string $currentValue = null): array
    {
        $factory = app(\BladeUI\Icons\Factory::class);
        $options = [];

        // 1. Selalu sertakan nilai saat ini jika ada dan valid
        if ($currentValue && self::isValidIcon($factory, $currentValue)) {
            $options[$currentValue] = $currentValue;
        }

        $search = strtolower($search);

        // 2. Jika pencarian kosong, tampilkan daftar default (icon umum untuk proses rekrutmen)
        if (empty($search)) {
            $commonIcons = [
                'heroicon-o-user',
                'heroicon-o-users',
                'heroicon-o-user-group',
                'heroicon-o-document-text',
                'heroicon-o-clipboard-document-list',
                'heroicon-o-newspaper',
                'heroicon-o-pencil',
                'heroicon-o-pencil-square',
                'heroicon-o-chat-bubble-left-right',
                'heroicon-o-chat-bubble-oval-left-ellipsis',
                'heroicon-o-presentation-chart-line',
                'heroicon-o-presentation-chart-bar',
                'heroicon-o-microphone',
                'heroicon-o-video-camera',
                'heroicon-o-academic-cap',
                'heroicon-o-briefcase',
            ];

            foreach ($commonIcons as $icon) {
                $options[$icon] = $icon;
            }

            return $options;
        }

        // 3. Logika pencarian - hanya sertakan icon yang valid
        $manifest = app(\BladeUI\Icons\IconsManifest::class)->getManifest($factory->all());
        $count = 0;

        foreach ($manifest as $set => $paths) {
            foreach ($paths as $icons) {
                foreach ($icons as $iconName) {
                    if (str_contains($iconName, $search) && self::isValidIcon($factory, $iconName)) {
                        $options[$iconName] = $iconName;
                        $count++;

                        if ($count >= 30) {
                            return $options;
                        }
                    }
                }
            }
        }

        return $options;
    }

    protected static function isValidIcon(\BladeUI\Icons\Factory $factory, string $name): bool
    {
        try {
            $factory->svg($name);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
