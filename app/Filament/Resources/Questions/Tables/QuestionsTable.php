<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->colors([
                        'primary' => 'technical',
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
                //
            ])
            ->recordActions([
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
