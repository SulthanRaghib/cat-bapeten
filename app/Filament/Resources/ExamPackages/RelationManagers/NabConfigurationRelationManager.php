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
 * Bidirectional sync architecture:
 *   - Units are DRIVEN by questions in the "Soal Ujian" tab
 *   - Indicator edits here propagate BACK to Master Data (`question_unit_indicators`)
 *   - On mount(), Smart Sync loads fresh data from master
 *   - On save, changes are written to both JSON and master table
 *
 * Buttons:
 *  – "🔄 Sinkronkan" Smart Sync: refresh units from attached questions
 *  – "💾 Simpan" Persist edits to JSON + sync back to Master Data
 */
class NabConfigurationRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Konfigurasi NAB & Kelulusan';

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
                        Section::make('Konfigurasi NAB & Kelulusan')
                            ->description(
                                'Daftar unit ditentukan otomatis dari soal di tab "Soal Ujian".'
                                    . ' Perubahan indikator di sini akan tersimpan ke Master Data saat klik "💾 Simpan".'
                            )
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->headerActions([
                                // ── Smart Sync: refresh units from questions ──
                                Action::make('syncFromQuestions')
                                    ->label('🔄 Sinkronkan')
                                    ->icon('heroicon-o-arrow-path')
                                    ->color('warning')
                                    ->action(function (): void {
                                        /** @var ExamPackage $record */
                                        $record = $this->getOwnerRecord();

                                        $result = app(NabSyncService::class)->smartSync($record);

                                        if (empty($result['configs'])) {
                                            Notification::make()
                                                ->warning()
                                                ->title('Tidak ada soal terkait')
                                                ->body('Pastikan soal sudah ditambahkan di tab "Soal Ujian" dan setiap soal memiliki Unit.')
                                                ->send();

                                            return;
                                        }

                                        // Build descriptive notification
                                        $parts = [];
                                        if ($result['added'] > 0) {
                                            $parts[] = "{$result['added']} unit baru ditambahkan";
                                        }
                                        if ($result['removed'] > 0) {
                                            $parts[] = "{$result['removed']} unit tidak relevan dihapus";
                                        }
                                        if ($result['kept'] > 0) {
                                            $parts[] = "{$result['kept']} unit dipertahankan";
                                        }

                                        $hasChanges = $result['added'] > 0 || $result['removed'] > 0;

                                        Notification::make()
                                            ->color($hasChanges ? 'success' : 'info')
                                            ->title($hasChanges ? 'Sinkronisasi Berhasil' : 'Data Sudah Sinkron')
                                            ->body(
                                                $hasChanges
                                                    ? implode(', ', $parts) . '. Total: ' . count($result['configs']) . ' unit.'
                                                    : 'Semua unit sudah sesuai dengan soal yang ada (' . count($result['configs']) . ' unit). Tidak ada perubahan.'
                                            )
                                            ->send();

                                        // Force page reload — Repeater caches child schemas in PHP
                                        $this->js('setTimeout(() => window.location.reload(), 600)');
                                    }),

                                // ── Save & sync to Master Data ──
                                Action::make('saveNabConfig')
                                    ->label('💾 Simpan')
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

                                        // Bidirectional sync: save to JSON + propagate to master data
                                        $result = app(NabSyncService::class)->saveWithMasterSync($record, $configs);

                                        // Build notification message
                                        $parts = [];
                                        if ($result['updated'] > 0) {
                                            $parts[] = "{$result['updated']} diperbarui";
                                        }
                                        if ($result['created'] > 0) {
                                            $parts[] = "{$result['created']} ditambahkan";
                                        }
                                        if ($result['deleted'] > 0) {
                                            $parts[] = "{$result['deleted']} dihapus";
                                        }

                                        $detail = ! empty($parts)
                                            ? ' Indikator master: ' . implode(', ', $parts) . '.'
                                            : '';

                                        Notification::make()
                                            ->success()
                                            ->title('Tersimpan')
                                            ->body('Konfigurasi NAB & Kelulusan berhasil disimpan.' . $detail)
                                            ->send();

                                        // Reload to reflect fresh indicator_ids
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
                                    ->helperText('Unit ditentukan oleh soal di tab "Soal Ujian". Untuk menambah/menghapus unit, kelola soal terlebih dahulu, lalu klik "🔄 Sinkronkan".')
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
                                            ->label('Level Indikator')
                                            ->addActionLabel('+ Tambah Indikator')
                                            ->collapsible()
                                            ->cloneable()
                                            ->defaultItems(0)
                                            ->deletable(true)
                                            ->helperText('Perubahan indikator akan tersinkron ke Master Data saat disimpan.')
                                            ->schema([
                                                Hidden::make('indicator_id'),

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

        // Always run Smart Sync on mount to ensure data is fresh from master
        $result = app(NabSyncService::class)->smartSync($record);

        $configs = $result['configs'];

        // Only notify on first auto-sync (when units were added from scratch)
        if ($result['added'] > 0 && $result['kept'] === 0 && ! empty($configs)) {
            Notification::make()
                ->info()
                ->title('Auto-Sync')
                ->body('Konfigurasi NAB otomatis di-sync dari ' . count($configs) . ' unit soal.')
                ->send();
        }

        $this->nabData = [
            'unit_scoring_configs' => $configs,
        ];
    }
}
