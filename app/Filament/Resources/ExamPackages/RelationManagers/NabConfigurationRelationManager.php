<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\ExamPackage;
use App\Models\QuestionUnit;
use App\Models\QuestionUnitIndicator;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
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
 * State management:
 *   - A public Livewire property `$nabData` holds the form state
 *   - A Group with `statePath('nabData')` scopes all components to this property
 *   - On mount(), we auto-sync from master data if no config exists yet
 *
 * Features:
 *  – Auto-sync from master data on first tab open (if empty)
 *  – "⚡ Sync dari Soal" button for manual re-sync
 *  – "💾 Simpan Konfigurasi" persists inline edits
 *  – Full inline editing of indicator levels per unit
 *  – Conditionally hidden for non-weighted (Teknis) exam types
 */
class NabConfigurationRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Konfigurasi NAB & Kelulusan';

    /**
     * Livewire state property — all form components bind to this
     * via Group::make()->statePath('nabData').
     *
     * @var array{unit_scoring_configs: array<int, array>}
     */
    public array $nabData = [];

    // ── Hide this tab unless the ExamPackage uses weighted evaluation ──
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var ExamPackage $ownerRecord */
        return $ownerRecord->examType?->evaluation_method === 'weighted';
    }

    // ── Mount: auto-sync from master data if no config exists yet ──
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

                // ── Wrap everything in a Group scoped to our Livewire property ──
                Group::make()
                    ->statePath('nabData')
                    ->schema([
                        Section::make('Konfigurasi NAB & Kelulusan')
                            ->description('Kelola konfigurasi penilaian per-unit untuk paket Mansoskul ini. Gunakan "⚡ Sync dari Soal" untuk me-refresh dari Master Data.')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                // ── Action Buttons ──
                                Actions::make([
                                    Action::make('syncFromQuestions')
                                        ->label('⚡ Sync dari Soal')
                                        ->icon('heroicon-o-arrow-path')
                                        ->color('warning')
                                        ->requiresConfirmation()
                                        ->modalHeading('Sinkronisasi Konfigurasi NAB')
                                        ->modalDescription('Ini akan menimpa seluruh konfigurasi NAB yang ada dengan template default dari Master Data Unit. Lanjutkan?')
                                        ->modalSubmitActionLabel('Ya, Sinkronkan')
                                        ->action(function (): void {
                                            $configs = $this->buildConfigsFromMasterData();

                                            if (empty($configs)) {
                                                Notification::make()
                                                    ->warning()
                                                    ->title('Tidak ada soal terkait')
                                                    ->body('Pastikan soal sudah ditambahkan di tab "Soal Ujian" dan setiap soal memiliki Unit.')
                                                    ->send();

                                                return;
                                            }

                                            // Persist to DB
                                            $this->getOwnerRecord()->update(['unit_scoring_configs' => $configs]);

                                            // Update local state (in case reload is delayed)
                                            $this->nabData = ['unit_scoring_configs' => $configs];

                                            Notification::make()
                                                ->success()
                                                ->title('Berhasil Disinkronisasi')
                                                ->body('Konfigurasi NAB berhasil disinkronisasi dari ' . count($configs) . ' unit soal. Halaman akan di-refresh...')
                                                ->send();

                                            // Force page reload to rebuild Repeater items from fresh DB state.
                                            // The Repeater caches child schemas in PHP; $set() inside a content()
                                            // override can't clear that cache reliably, so we reload to re-mount.
                                            $this->js('setTimeout(() => window.location.reload(), 600)');
                                        }),

                                    Action::make('saveNabConfig')
                                        ->label('💾 Simpan Konfigurasi')
                                        ->icon('heroicon-o-check-circle')
                                        ->color('primary')
                                        ->action(function (): void {
                                            // Read directly from the Livewire property (wire:model keeps it in sync).
                                            // Strip Repeater UUID keys so we store clean numeric arrays in JSON.
                                            $configs = collect($this->nabData['unit_scoring_configs'] ?? [])
                                                ->values()
                                                ->map(function (array $unit): array {
                                                    $unit['indicators'] = array_values($unit['indicators'] ?? []);

                                                    return $unit;
                                                })
                                                ->toArray();

                                            $this->getOwnerRecord()->update(['unit_scoring_configs' => $configs]);

                                            Notification::make()
                                                ->success()
                                                ->title('Tersimpan')
                                                ->body('Konfigurasi NAB & Kelulusan berhasil disimpan.')
                                                ->send();
                                        }),
                                ]),

                                // ── Outer Repeater: one item per Unit ──
                                Repeater::make('unit_scoring_configs')
                                    ->label('')
                                    ->addable(false)
                                    ->deletable(true)
                                    ->reorderable(false)
                                    ->defaultItems(0)
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('unit_name')
                                                ->label('Nama Unit')
                                                ->disabled()
                                                ->dehydrated()
                                                ->columnSpan(1),

                                            Hidden::make('question_unit_id'),
                                        ]),

                                        // ── Inner Repeater: indicator levels per unit ──
                                        Repeater::make('indicators')
                                            ->label('Level Indikator')
                                            ->addActionLabel('+ Tambah Indikator')
                                            ->collapsible()
                                            ->cloneable()
                                            ->defaultItems(0)
                                            ->schema([
                                                Grid::make(4)->schema([
                                                    TextInput::make('name')
                                                        ->label('Nama Indikator')
                                                        ->required()
                                                        ->placeholder('cth: Memenuhi Standar')
                                                        ->columnSpan(2),

                                                    TextInput::make('min_score')
                                                        ->label('Skor Min')
                                                        ->numeric()
                                                        ->required()
                                                        ->columnSpan(1),

                                                    TextInput::make('max_score')
                                                        ->label('Skor Max')
                                                        ->numeric()
                                                        ->required()
                                                        ->columnSpan(1),
                                                ]),

                                                Toggle::make('is_passing')
                                                    ->label('Lulus NAB?')
                                                    ->default(false)
                                                    ->helperText('Tandai jika indikator ini memenuhi syarat kelulusan.'),
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

    // ── Unused but required by RelationManager contract ──
    public function table(Table $table): Table
    {
        return $table->columns([])->paginated(false);
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([]);
    }

    // ─── Load state from DB, auto-sync if empty ────────────────────────

    protected function loadNabData(): void
    {
        /** @var ExamPackage $record */
        $record = $this->getOwnerRecord();
        $configs = $record->unit_scoring_configs;

        // Auto-sync from master data if no config exists yet
        if (empty($configs)) {
            $configs = $this->buildConfigsFromMasterData();

            if (! empty($configs)) {
                $record->update(['unit_scoring_configs' => $configs]);

                Notification::make()
                    ->info()
                    ->title('Auto-Sync')
                    ->body('Konfigurasi NAB otomatis di-sync dari ' . count($configs) . ' unit soal.')
                    ->send();
            }
        }

        $this->nabData = [
            'unit_scoring_configs' => $configs ?? [],
        ];
    }

    // ─── Build config array from master data (questions → units → indicators) ──

    protected function buildConfigsFromMasterData(): array
    {
        /** @var ExamPackage $record */
        $record = $this->getOwnerRecord()->fresh();

        $unitIds = $record->questions()
            ->whereNotNull('questions.question_unit_id')
            ->pluck('questions.question_unit_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($unitIds)) {
            return [];
        }

        $units = QuestionUnit::with('indicators')
            ->whereIn('id', $unitIds)
            ->orderBy('name')
            ->get();

        return $units->map(fn(QuestionUnit $unit): array => [
            'question_unit_id' => $unit->id,
            'unit_name'        => $unit->name,
            'indicators'       => $unit->indicators
                ->map(fn(QuestionUnitIndicator $ind): array => [
                    'name'       => $ind->name,
                    'min_score'  => $ind->min_score,
                    'max_score'  => $ind->max_score,
                    'is_passing' => $ind->is_passing,
                ])
                ->values()
                ->toArray(),
        ])->values()->toArray();
    }
}
