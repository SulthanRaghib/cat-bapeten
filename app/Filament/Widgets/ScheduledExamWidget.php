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

use App\Models\ExamSession;
use Illuminate\Contracts\View\View;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ScheduledExamWidget extends BaseWidget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = 2; // Position below stats overview
    protected int | string | array $columnSpan = 'full';

    // Auto-refresh widget every 5 seconds to keep status/counts updated real-time
    protected static ?string $pollingInterval = '3s';

    public function getHeading(): string
    {
        return 'Daftar Ujian Terjadwal';
    }

    public function getDescription(): string | View | null
    {
        // 1. Ujian Sedang Berlangsung Tanpa Aktivitas (Peserta Belum Login)
        // Menggantikan "Ujian Tanpa Peserta" dan "Ujian Belum Publish Hasil"
        // Kondisi: Sedang berlangsung (ongoing) TAPI tidak ada sesi ujian yang terbentuk (artinya belum ada peserta login)
        $examsOngoingNoActivity = ExamPackage::query()
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->doesntHave('examSessions')
            ->count();

        // 2. Ujian Mendekati Waktu Mulai
        $examsStartingSoon = ExamPackage::query()
            ->where('is_active', true)
            ->where('start_time', '>', now())
            ->where('start_time', '<=', now()->addHour())
            ->count();

        // 3. Peserta logout paksa/terminated dalam 24 jam terakhir
        $participantsLoggedOut = ExamSession::query()
            ->where('status', 'terminated')
            ->where('updated_at', '>=', now()->subDay())
            ->count();

        if ($examsOngoingNoActivity === 0 && $examsStartingSoon === 0 && $participantsLoggedOut === 0) {
            return null;
        }

        return view('filament.widgets.exam-alerts', compact(
            'examsOngoingNoActivity',
            'examsStartingSoon',
            'participantsLoggedOut'
        ));
    }

    public function viewNoActivityAction(): Action
    {
        return Action::make('viewNoActivity')
            ->label('Ujian Tanpa Aktivitas')
            ->modalHeading('Daftar Ujian Sedang Berlangsung Tanpa Aktivitas')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(function () {
                $exams = ExamPackage::query()
                    ->where('is_active', true)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->doesntHave('examSessions')
                    ->get();

                return view('filament.widgets.modals.exam-list', [
                    'records' => $exams,
                    'actionLabel' => 'Periksa',
                    'actionUrl' => fn($record) => ExamPackageResource::getUrl('edit', ['record' => $record]),
                ]);
            });
    }

    public function viewStartingSoonAction(): Action
    {
        return Action::make('viewStartingSoon')
            ->label('Ujian Segera Mulai')
            ->modalHeading('Daftar Ujian Segera Mulai')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(function () {
                $exams = ExamPackage::where('is_active', true)
                    ->where('start_time', '>', now())
                    ->where('start_time', '<=', now()->addHour())
                    ->get();
                return view('filament.widgets.modals.exam-list', [
                    'records' => $exams,
                    'actionLabel' => 'Lihat Detail',
                    'actionUrl' => fn($record) => ExamPackageResource::getUrl('edit', ['record' => $record]),
                ]);
            });
    }

    public function viewLoggedOutAction(): Action
    {
        return Action::make('viewLoggedOut')
            ->label('Peserta Terdeteksi Logout')
            ->modalHeading('Daftar Peserta Terdeteksi Logout (24 Jam Terakhir)')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalContent(function () {
                $sessions = ExamSession::where('status', 'terminated')
                    ->where('updated_at', '>=', now()->subDay())
                    ->with(['user', 'examPackage'])
                    ->latest('updated_at')
                    ->get();
                return view('filament.widgets.modals.session-list', [
                    'records' => $sessions,
                    'actionUrl' => fn($session) => ExamMonitorResource::getUrl('index'),
                ]);
            });
    }

    public function table(Table $table): Table
    {
        $ongoingCount = ExamPackage::query()
            ->where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->count();

        $description = $this->getDescription();
        $ongoingMessage = $ongoingCount > 0 ? "🔴 {$ongoingCount} Ujian Sedang Berlangsung" : null;

        $finalDescription = null;
        if ($description && $ongoingMessage) {
            $finalDescription = new \Illuminate\Support\HtmlString((string) $description . '<br>' . $ongoingMessage);
        } elseif ($description) {
            $finalDescription = $description;
        } else {
            $finalDescription = $ongoingMessage;
        }

        return $table
            ->poll('5s') // Refresh every 5 seconds
            ->description($finalDescription)
            ->query(
                ExamPackage::query()
                    ->latest('start_time')
                    ->limit(5)
                    ->withCount([
                        'participants', // Ensures 'participants_count' is available
                        'examParticipants as participants_finished_count' => function ($query) {
                            $query->whereHas('examSessions', function ($q) {
                                $q->where('status', 'completed');
                            });
                        }
                    ])
            )
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
                        ($record->start_time && $record->end_time && $record->start_time->isSameDay($record->end_time))
                            ? ($record->start_time->format('d M Y') . '<br>' .
                                '<span class="text-xs text-gray-500">' .
                                $record->start_time->format('H:i') . ' - ' .
                                $record->end_time->format('H:i') . ' WIB' .
                                '</span>')
                            : '<div class="text-xs">' .
                            ($record->start_time ? $record->start_time->format('d M Y H:i') : '-') . ' - <br>' .
                            ($record->end_time ? $record->end_time->format('d M Y H:i') : '-') . ' WIB' .
                            '</div>'
                    ))
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn($state) => "{$state} menit")
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Peserta')
                    ->formatStateUsing(function (ExamPackage $record) {
                        $total = $record->participants_count ?? 0;
                        $finished = $record->participants_finished_count ?? 0;
                        $percentage = $total > 0 ? round(($finished / $total) * 100) : 0;

                        return new \Illuminate\Support\HtmlString(
                            "<div class='mb-1'>{$total} Peserta</div>" .
                                ($total > 0
                                    ? "<div class='text-xs text-gray-500 mb-1'>{$finished} Selesai ({$percentage}%)</div>
                                   <div class='w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700 overflow-hidden'>
                                       <div class='bg-success-600 h-1.5 rounded-full' style='width: {$percentage}%'></div>
                                   </div>"
                                    : "")
                        );
                    })
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
                        }
                    }),
            ]);
    }
}
