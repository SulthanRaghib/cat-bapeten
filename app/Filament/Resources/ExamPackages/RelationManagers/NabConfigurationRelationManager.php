<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\ExamPackage;
use App\Services\NabSyncService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom RelationManager tab for per-package NAB configuration (Mansoskul).
 *
 * Instead of displaying a table, this overrides content() to show a
 * Repeater-based form that manages the JSON `unit_scoring_configs`
 * column on ExamPackage.
 *
 * Per-package independent architecture:
 *   - Units are DRIVEN by questions in the "Soal Ujian" tab.
 *   - Indicator names come from Master Data as a starting template (read-only reference).
 *   - Each package owns its min_score/max_score/is_passing values INDEPENDENTLY.
 *   - Saving a package NEVER writes back to the master data table.
 *   - Per-unit "↩ Reset ke Master" restores the original master template for that unit.
 *
 * Buttons:
 *  – "🔄 Sinkronkan" Smart Sync: add new units / remove stale units (preserves custom values)
 *  – "💾 Simpan"      Save per-package config to JSON column only
 *  – "↩ Reset ke Master" (per-unit) Revert one unit's indicators to the master template
 */
class NabConfigurationRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = null;

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('NAB & Graduation Configuration');
    }

    /**
     * Livewire state property — all form components bind to this
     * via Group::make()->statePath('nabData').
     */
    public array $nabData = [];

    // ── Hide this tab unless the ExamPackage uses weighted evaluation ──
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var ExamPackage $ownerRecord */
        return $ownerRecord->examType?->evaluation_method === 'weighted';
    }

    // ── Mount: always Smart Sync to ensure data is fresh from master ──
    public function mount(): void
    {
        parent::mount();
        $this->loadNabData();
    }

    // ── Override content() to replace EmbeddedTable with custom form ──
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_BEFORE),

                Group::make()
                    ->statePath('nabData')
                    ->schema([
                        Section::make(__('NAB & Graduation Configuration'))
                            ->description(
                                __('The unit list is determined automatically from questions in the "Exam Questions" tab.')
                                    . ' ' . __('Indicator values are independent per exam package — changes here only apply to this package and do not affect other packages or Master Data.')
                            )
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->headerActions([
                                // ── Smart Sync: refresh units from questions ──
                                Action::make('syncFromQuestions')
                                    ->label('🔄 ' . __('Synchronize'))
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('warning')
                                    ->action(function (): void {
                                        /** @var ExamPackage $record */
                                        $record = $this->getOwnerRecord();

                                        $result = app(NabSyncService::class)->smartSync($record);

                                        if (empty($result['configs'])) {
                                            Notification::make()
                                                ->warning()
                                                ->title(__('No related questions'))
                                                ->body(__('Make sure questions have been added in the "Exam Questions" tab and each question has a Unit.'))
                                                ->send();

                                            return;
                                        }

                                        // Build descriptive notification
                                        $parts = [];
                                        if ($result['added'] > 0) {
                                            $parts[] = __(':count new units added', ['count' => $result['added']]);
                                        }
                                        if ($result['removed'] > 0) {
                                            $parts[] = __(':count irrelevant units removed', ['count' => $result['removed']]);
                                        }
                                        if ($result['kept'] > 0) {
                                            $parts[] = __(':count units retained', ['count' => $result['kept']]);
                                        }

                                        $hasChanges = $result['added'] > 0 || $result['removed'] > 0;

                                        Notification::make()
                                            ->color($hasChanges ? 'success' : 'info')
                                            ->title($hasChanges ? __('Sync Successful') : __('Data Already Synced'))
                                            ->body(
                                                $hasChanges
                                                    ? implode(', ', $parts) . '. Total: ' . count($result['configs']) . ' unit.'
                                                    : 'Semua unit sudah sesuai dengan soal yang ada (' . count($result['configs']) . ' unit). Tidak ada perubahan.'
                                            )
                                            ->send();

                                        // Force page reload — Repeater caches child schemas in PHP
                                        $this->js('setTimeout(() => window.location.reload(), 600)');
                                    }),

                                // ── Save per-package config (JSON only — no master sync) ──
                                Action::make('saveNabConfig')
                                    ->label('💾 ' . __('Save'))
                                    ->icon('heroicon-o-check-circle')
                                    ->color('primary')
                                    ->action(function (): void {
                                        $configs = collect($this->nabData['unit_scoring_configs'] ?? [])
                                            ->values()
                                            ->map(function (array $unit): array {
                                                $unit['indicators'] = array_values($unit['indicators'] ?? []);

                                                return $unit;
                                            })
                                            ->toArray();

                                        /** @var ExamPackage $record */
                                        $record = $this->getOwnerRecord();

                                        // Save to package JSON column only — does not touch master data
                                        app(NabSyncService::class)->savePackageConfig($record, $configs);

                                        Notification::make()
                                            ->success()
                                            ->title(__('Saved'))
                                            ->body(__('NAB & Graduation Configuration saved for this package.'))
                                            ->send();

                                        $this->js('setTimeout(() => window.location.reload(), 600)');
                                    }),
                            ])
                            ->schema([
                                Repeater::make('unit_scoring_configs')
                                    ->label('')
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->defaultItems(0)
                                    ->helperText(__('Units are determined by questions in the "Exam Questions" tab. To add/remove units, manage questions first, then click "Synchronize". Customized indicator values will not change during synchronization.'))
                                    ->extraItemActions([
                                        Action::make('resetUnitToMaster')
                                            ->label('↩ ' . __('Reset to Master'))
                                            ->icon('heroicon-o-arrow-uturn-left')
                                            ->color('warning')
                                            ->requiresConfirmation()
                                            ->modalHeading(__('Reset to Master Values?'))
                                            ->modalDescription(__('The indicator values for this unit will be reset to the master data template. Customizations made to this unit will be lost.'))
                                            ->action(function (array $arguments): void {
                                                $itemKey = $arguments['item'];
                                                $unitId  = (int) ($this->nabData['unit_scoring_configs'][$itemKey]['question_unit_id'] ?? 0);

                                                if ($unitId === 0) {
                                                    return;
                                                }

                                                /** @var ExamPackage $record */
                                                $record = $this->getOwnerRecord();

                                                $updatedConfigs = app(NabSyncService::class)->resetUnitToMaster($record, $unitId);

                                                $this->nabData = ['unit_scoring_configs' => $updatedConfigs];

                                                Notification::make()
                                                    ->success()
                                                    ->title(__('Reset Successful'))
                                                    ->body(__('Unit indicators have been reset to the master data template.'))
                                                    ->send();

                                                $this->js('setTimeout(() => window.location.reload(), 600)');
                                            }),
                                    ])
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('unit_name')
                                                ->label('Nama Unit')
                                                ->disabled()
                                                ->dehydrated()
                                                ->columnSpan(1),

                                            Hidden::make('question_unit_id'),
                                        ]),

                                        Repeater::make('indicators')
                                            ->label(__('Indicator Levels'))
                                            ->addActionLabel(__('+ Add Indicator'))
                                            ->collapsible()
                                            ->cloneable()
                                            ->defaultItems(0)
                                            ->deletable(true)
                                            ->helperText('Tentukan rentang skor dan status kelulusan untuk paket ini. Perubahan tidak mempengaruhi paket ujian lain.')
                                            ->schema([
                                                Grid::make(4)->schema([
                                                    TextInput::make('name')
                                                        ->label(__('Indicator Name'))
                                                        ->validationAttribute(__('Indicator Name'))
                                                        ->required()
                                                        ->placeholder(__('e.g. Meets Standard'))
                                                        ->columnSpan(2),

                                                    TextInput::make('min_score')
                                                        ->label(__('Min Score'))
                                                        ->validationAttribute('Skor Minimal')
                                                        ->numeric()
                                                        ->required()
                                                        ->columnSpan(1),

                                                    TextInput::make('max_score')
                                                        ->label(__('Max Score'))
                                                        ->validationAttribute('Skor Maks')
                                                        ->numeric()
                                                        ->required()
                                                        ->columnSpan(1),
                                                ]),

                                                Toggle::make('is_passing')
                                                    ->label(__('Pass NAB?'))
                                                    ->default(false)
                                                    ->helperText(__('Mark if this indicator meets the graduation requirement.')),
                                            ])
                                            ->itemLabel(fn(array $state): ?string => ($state['name'] ?? 'Indikator baru')
                                                . ' (' . ($state['min_score'] ?? '?') . '–' . ($state['max_score'] ?? '?') . ')'
                                                . (($state['is_passing'] ?? false) ? ' ✅' : '')),
                                    ])
                                    ->itemLabel(fn(array $state): ?string => $state['unit_name'] ?? 'Unit'),
                            ]),
                    ]),

                RenderHook::make(PanelsRenderHook::RESOURCE_RELATION_MANAGER_AFTER),
            ]);
    }

    // ── Required by RelationManager contract ──
    public function table(Table $table): Table
    {
        return $table->columns([])->paginated(false);
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([]);
    }

    // ─── Load state from DB — always Smart Sync on mount ───────────────

    protected function loadNabData(): void
    {
        /** @var ExamPackage $record */
        $record = $this->getOwnerRecord();

        // Run Smart Sync on mount: adds new units from questions, removes stale units.
        // Per-package indicator values are preserved for existing units.
        $result = app(NabSyncService::class)->smartSync($record);

        $configs = $result['configs'];

        // Only notify on first auto-sync (when units were added from scratch)
        if ($result['added'] > 0 && $result['kept'] === 0 && ! empty($configs)) {
            Notification::make()
                ->info()
                ->title(__('Auto-Sync'))
                ->body(__('NAB configuration auto-synced from :count question units.', ['count' => count($configs)]))
                ->send();
        }

        $this->nabData = [
            'unit_scoring_configs' => $configs,
        ];
    }
}
