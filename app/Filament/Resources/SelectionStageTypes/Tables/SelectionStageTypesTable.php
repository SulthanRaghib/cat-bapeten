<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Tables;

use App\Models\SelectionStageType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SelectionStageTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                // Sort handle hint
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50)
                    ->alignCenter(),

                // Icon preview
                IconColumn::make('icon')
                    ->label('Icon')
                    ->icon(fn(?string $state): string => $state ?? 'heroicon-o-tag')
                    ->color('primary')
                    ->alignCenter()
                    ->width(60),

                TextColumn::make('name')
                    ->label('Nama Tahap')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('description')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->alignCenter()
                    ->width(80),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Jenis Tahap'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada jenis tahap seleksi')
            ->emptyStateDescription('Tambahkan jenis tahap seleksi yang digunakan dalam proses rekrutmen, seperti Wawancara, FGD, atau Presentasi.')
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}
