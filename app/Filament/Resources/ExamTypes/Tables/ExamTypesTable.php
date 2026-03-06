<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamTypes\Tables;

use App\Models\ExamType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->recordUrl(null)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Tipe Ujian')
                    ->description(fn(ExamType $record): string => 'Kode: ' . $record->code)
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('evaluation_method')
                    ->label('Metode Evaluasi')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'heroicon-m-check-badge',
                        'weighted'      => 'heroicon-m-chart-bar',
                        default         => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'info',
                        'weighted'      => 'primary',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'correct_wrong' => 'Benar / Salah',
                        'weighted'      => 'Berbobot',
                        default         => $state,
                    })
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->icon('heroicon-m-document-text')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('exam_packages_count')
                    ->label('Jumlah Paket')
                    ->counts('examPackages')
                    ->icon('heroicon-m-rectangle-stack')
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
            ->recordActions([
                EditAction::make()
                    ->label('Edit Tipe Ujian')
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->emptyStateHeading('Belum ada tipe ujian')
            ->emptyStateDescription('Tambahkan tipe ujian seperti Teknis atau Mansoskul untuk mulai mengelola paket ujian.')
            ->emptyStateIcon('heroicon-o-academic-cap');
    }
}
