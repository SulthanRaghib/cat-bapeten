<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Manages the default/template indicator levels (NAB) for each QuestionUnit.
 *
 * Admins use this to set up the master data that will be synced
 * into ExamPackage JSON snapshots via the "Sync dari Soal" action.
 */
class IndicatorsRelationManager extends RelationManager
{
    protected static string $relationship = 'indicators';

    protected static ?string $title = 'Indikator NAB (Template)';
    protected static ?string $modelLabel = 'Indikator';

    // Hanya tampilkan tab ini untuk unit bertipe Mansoskul (weighted)
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->examType?->evaluation_method === 'weighted';
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Level Indikator')
                    ->placeholder('cth: Memenuhi Standar')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('min_score')
                    ->label('Skor Minimum')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                TextInput::make('max_score')
                    ->label('Skor Maksimum')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                Toggle::make('is_passing')
                    ->label('Termasuk Lulus NAB?')
                    ->helperText('Tandai jika peserta dengan indikator ini dianggap lulus.')
                    ->default(false)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0)
                    ->helperText('Urutan tampil — semakin kecil semakin atas.'),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('name')
                    ->label('Nama Indikator')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('min_score')
                    ->label('Min')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('max_score')
                    ->label('Maks')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('range')
                    ->label('Rentang Skor')
                    ->getStateUsing(fn($record): string => "{$record->min_score} – {$record->max_score}")
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_passing')
                    ->label('Lulus?')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Indikator')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Indikator NAB')
                    ->modalSubmitActionLabel('Simpan Indikator'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Ubah Data Indikator')
                    ->modalSubmitActionLabel('Simpan Perubahan'),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Indikator?')
                    ->modalDescription('Apakah Anda yakin ingin menghapus indikator ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Hapus Terpilih')
                    ->modalHeading('Hapus Indikator Terpilih?')
                    ->modalDescription('Apakah Anda yakin ingin menghapus indikator yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->emptyStateHeading('Belum ada indikator')
            ->emptyStateDescription('Tambahkan level indikator NAB untuk unit ini. Data ini akan menjadi template default saat di-sync ke Paket Ujian.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
