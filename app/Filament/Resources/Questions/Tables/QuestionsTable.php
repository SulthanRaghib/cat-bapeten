<?php

namespace App\Filament\Resources\Questions\Tables;

use App\Filament\Actions\ExportQuestionsBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
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
                    }),

                TextColumn::make('question_text')
                    ->label('Teks Soal')
                    ->html()
                    ->limit(60)
                    ->wrap()
                    ->tooltip(function (TextColumn $column): ?string {
                        try {
                            $state = $column->getState();
                            if (strlen($state) <= 60) {
                                return null;
                            }
                            return strip_tags($state);
                        } catch (\Exception $e) {
                            return null;
                        }
                    }),

                TextColumn::make('category')
                    ->label('Tingkat Kesulitan')
                    ->badge()
                    ->icon(fn(?string $state): string => match ($state) {
                        'easy'   => 'heroicon-m-face-smile',
                        'medium' => 'heroicon-m-minus-circle',
                        'hard'   => 'heroicon-m-fire',
                        default  => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'easy'   => 'success',
                        'medium' => 'warning',
                        'hard'   => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'easy'   => 'Mudah',
                        'medium' => 'Sedang',
                        'hard'   => 'Sulit',
                        default  => '\u2014',
                    })
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exam_type_id')
                    ->label('Tipe Ujian')
                    ->relationship('examType', 'name'),
                SelectFilter::make('category')
                    ->label('Tingkat Kesulitan')
                    ->options([
                        'easy'   => 'Mudah',
                        'medium' => 'Sedang',
                        'hard'   => 'Sulit',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->color('gray')
                        ->label('Lihat Detail')
                        ->modalHeading('Detail Soal')
                        ->modalContent(fn($record) => view('filament.modals.question-detail', [
                            'record' => $record,
                            'manager' => new \App\Helpers\ScoringConfigFormatter(),
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup'),
                    EditAction::make()
                        ->label('Edit Soal'),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(\Filament\Support\Enums\Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportQuestionsBulkAction::make(),
                    DeleteBulkAction::make()
                        ->label('Hapus Soal Terpilih')
                        ->modalHeading('Hapus Soal Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus soal-soal yang dipilih ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])->label('Tindakan Massal'),
            ])
            ->emptyStateHeading('Belum ada soal')
            ->emptyStateDescription('Tambahkan soal baru ke bank soal untuk digunakan dalam paket ujian.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
