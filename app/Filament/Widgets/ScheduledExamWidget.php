<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ExamMonitors\ExamMonitorResource;
use App\Filament\Resources\ExamPackages\ExamPackageResource;
use App\Models\ExamPackage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ScheduledExamWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Position below stats overview
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Daftar Ujian Terjadwal';
    }

    // public function getDescription(): ?string
    // {
    //     $ongoingCount = ExamPackage::query()
    //         ->where('is_active', true)
    //         ->where('start_time', '<=', now())
    //         ->where('end_time', '>=', now())
    //         ->count();

    //     if ($ongoingCount > 0) {
    //         return "🔴 {$ongoingCount} Ujian Sedang Berlangsung";
    //     }

    //     return null; // Or "Daftar ujian yang akan datang dan sedang berlangsung"
    // }

    public function table(Table $table): Table
    {
        $ongoingCount = ExamPackage::query()
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        return $table
            ->query(
                ExamPackage::query()
                    ->latest('start_time')
                    ->limit(5)
            )
            ->description($ongoingCount > 0 ? "🔴 {$ongoingCount} Ujian Sedang Berlangsung" : null)
            ->headerActions([
                Action::make('monitoring')
                    ->label('Monitoring Ujian')
                    ->icon('heroicon-m-eye')
                    ->color('danger')
                    ->url(ExamMonitorResource::getUrl('index'))
                    ->visible($ongoingCount > 0),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Ujian')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Pelaksanaan')
                    ->formatStateUsing(fn(ExamPackage $record) => new \Illuminate\Support\HtmlString(
                        ($record->start_time ? $record->start_time->format('d M Y') : '-') . '<br>' .
                            '<span class="text-xs text-gray-500">' .
                            ($record->start_time ? $record->start_time->format('H:i') : '?') . ' - ' .
                            ($record->end_time ? $record->end_time->format('H:i') : '?') . ' WIB' .
                            '</span>'
                    ))
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn($state) => "{$state} menit")
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Total Peserta')
                    ->counts('participants')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('computed_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'success',
                        'ongoing'   => 'primary',
                        'finished'  => 'gray',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Akan Datang',
                        'ongoing'   => 'Berlangsung',
                        'finished'  => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    })
                    ->alignment('center'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit Ujian')
                    ->url(fn(ExamPackage $record): string => ExamPackageResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Belum ada ujian terjadwal.')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Akan Datang',
                        'ongoing'   => 'Berlangsung',
                        'finished'  => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        $status = $data['value'];
                        $now = now();

                        if ($status === 'scheduled') {
                            $query->where('is_active', true)
                                ->where('start_time', '>', $now);
                        } elseif ($status === 'ongoing') {
                            $query->where('is_active', true)
                                ->where('start_time', '<=', $now)
                                ->where('end_time', '>=', $now);
                        } elseif ($status === 'finished') {
                            $query->where('end_time', '<', $now);
                        } elseif ($status === 'cancelled') {
                            $query->where('is_active', false);
                        }
                    }),
            ]);
    }
}
