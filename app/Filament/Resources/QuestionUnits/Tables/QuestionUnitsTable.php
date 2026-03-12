<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuestionUnits\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(null)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('examType.name')
                    ->label('Tipe Ujian')
                    ->badge()
                    ->icon(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'heroicon-m-check-badge',
                        'weighted'      => 'heroicon-m-chart-bar',
                        default         => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn($record): string => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('sub_units_count')
                    ->label('Sub Unit')
                    ->counts('subUnits')
                    ->icon('heroicon-m-bars-3-bottom-left')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Soal')
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_type_id')
                    ->label('Tipe Ujian')
                    ->relationship('examType', 'name')
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit Unit')
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('kelola_indikator')
                        ->label('Kelola Indikator NAB')
                        ->icon('heroicon-m-chart-bar')
                        ->color('primary')
                        ->url(fn($record) => url("/admin/question-units/{$record->id}/edit?relation=0"))
                        ->visible(fn($record): bool => $record->examType?->evaluation_method === 'weighted'),

                    DeleteAction::make()
                        ->label('Hapus Unit')
                        ->icon('heroicon-m-trash')
                        ->modalHeading('Hapus Unit Soal?')
                        ->modalDescription('Menghapus unit ini juga akan menghapus seluruh sub unit dan soal yang terkait. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->emptyStateHeading('Belum ada unit soal')
            ->emptyStateDescription('Tambahkan unit soal untuk mengelompokkan soal-soal berdasarkan kompetensi.')
            ->emptyStateIcon('heroicon-o-folder');
    }
}
