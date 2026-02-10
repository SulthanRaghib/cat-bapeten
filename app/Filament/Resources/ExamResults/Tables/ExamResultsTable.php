<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Models\ExamSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExamResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('user.name')
                    ->label('Peserta')
                    ->description(fn(ExamSession $record) => $record->user->nip ?? '-')
                    ->searchable(['name', 'nip']), // Assuming 'user.name' and scope logic handles it or global search
                TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('finished_at')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Durasi')
                    ->state(function (ExamSession $record) {
                        if ($record->started_at && $record->finished_at) {
                            return $record->started_at->diffForHumans($record->finished_at, true);
                        }
                        return '-';
                    }),
                TextColumn::make('total_score')
                    ->label('Nilai Akhir')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state >= 75 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->icon(fn(string $state): ?string => $state >= 75 ? 'heroicon-m-check-circle' : null)
                    ->sortable(),
                TextColumn::make('status_lulus')
                    ->label('Status')
                    ->badge()
                    ->state(fn(ExamSession $record) => $record->total_score >= 70 ? 'Lulus' : 'Tidak Lulus')
                    ->color(fn(string $state): string => match ($state) {
                        'Lulus' => 'success',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('exam_package_id')
                    ->label('Paket Ujian')
                    ->relationship('examPackage', 'title'),
                Filter::make('created_at')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('score_category')
                    ->label('Kategori Nilai')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'passed' => 'Passed (> 70)',
                                'failed' => 'Failed (< 70)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['status'], function (Builder $query, $status) {
                            if ($status === 'passed') {
                                $query->where('total_score', '>=', 70);
                            } elseif ($status === 'failed') {
                                $query->where('total_score', '<', 70);
                            }
                        });
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Result_Report_' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('examPackage.title')->heading('Exam Title'),
                                Column::make('user.name')->heading('Name'),
                                Column::make('user.nip')->heading('NIP'),
                                Column::make('finished_at')->heading('Date'),
                                Column::make('total_score')->heading('Score'),
                                Column::make('status_lulus')
                                    ->heading('Status')
                                    ->formatStateUsing(fn($record) => $record->total_score >= 70 ? 'Lulus' : 'Tidak Lulus'),
                            ]),
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Hasil Terpilih')
                        ->modalHeading('Hapus Hasil Ujian Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus hasil ujian terpilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                ])
                    ->label('Tindakan pada Hasil Terpilih'),

            ]);
    }
}
