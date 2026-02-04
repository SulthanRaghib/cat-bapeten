<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'info' => 'technical',
                        'warning' => 'structural',
                    ]),

                TextColumn::make('question_text')
                    ->label('Soal')
                    ->html()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        try {
                            $state = $column->getState();
                            if (strlen($state) <= 50) {
                                return null;
                            }
                            return strip_tags($state);
                        } catch (\Exception $e) {
                            return null;
                        }
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Soal')
                    ->options([
                        'technical' => 'Technical (Benar/Salah)',
                        'structural' => 'Structural (Bobot)',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'easy' => 'Mudah',
                        'medium' => 'Sedang',
                        'hard' => 'Sulit',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Pertanyaan')
                    ->modalContent(fn($record) => view('filament.modals.question-detail', [
                        'record' => $record,
                        // Lightweight manager substitute providing formatScoringConfig()
                        'manager' => new \App\Helpers\ScoringConfigFormatter(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                EditAction::make()
                    ->label('Edit Soal'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Soal Terpilih')
                        ->modalHeading('Hapus Soal Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus soal-soal yang dipilih ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ]),
            ]);
    }
}
