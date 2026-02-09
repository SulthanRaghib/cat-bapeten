<?php

namespace App\Filament\Widgets;

use App\Models\ExamSession;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LiveProctoringTable extends BaseWidget
{
    // Widget settings
    protected static ?string $heading = 'Monitoring Ujian Live';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2; // Position in dashboard

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExamSession::query()
                    ->with(['user', 'examPackage'])
                    ->where('status', 'ongoing')
                    ->latest('started_at')
            )
            ->poll('5s') // Real-time update
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('examPackage.title')
                    ->label('Paket Ujian')
                    ->limit(30)
                    ->tooltip(fn($state) => $state),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->since()
                    ->color('gray')
                    ->sortable(),

                // Custom Progress Column logic simplified heavily for performance
                // Assumes 'answers_meta' (array of question IDs) represents total questions
                // and we count related 'examAnswers' (if relationship exists) or simplified if not.
                // Assuming 'examAnswers' relationship exists on ExamSession.
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (ExamSession $record): string {
                        // Total questions assigned (from metadata)
                        $meta = $record->answers_meta ?? [];
                        $total = is_array($meta) ? count($meta) : 0;

                        if ($total === 0) return '0%';

                        // Count answers that are not null/empty
                        // This requires the relationship 'answers' (correct name in Model) to be loaded or counted
                        $answered = $record->answers()->count();

                        $pct = round(($answered / $total) * 100);
                        return "{$answered} / {$total} ({$pct}%)";
                    })
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, '100%') => 'success',
                        str_contains($state, '0%') => 'gray',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                Action::make('force_finish')
                    ->label('Paksa Selesai')
                    ->color('danger')
                    ->icon('heroicon-m-stop-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Paksa Selesai Ujian')
                    ->modalDescription('Apakah Anda yakin ingin mengakhiri sesi ujian peserta ini secara paksa? Jawaban yang tersimpan akan dikalkulasi.')
                    ->action(function (ExamSession $record) {
                        $record->update([
                            'status' => 'completed',
                            'finished_at' => now(),
                        ]);

                        if ($record->examParticipant && $record->examParticipant->is_active) {
                            $record->examParticipant->update(['is_active' => false]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Sesi ujian diakhiri paksa')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Tidak ada peserta yang sedang ujian')
            ->emptyStateDescription('Saat ini tidak ada sesi ujian yang berstatus ongoing.')
            ->paginated(false); // Show all active or limit simple
    }
}
