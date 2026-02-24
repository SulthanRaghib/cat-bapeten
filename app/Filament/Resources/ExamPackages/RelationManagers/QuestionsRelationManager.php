<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\ExamPackage;
use App\Models\Question;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    // Translasi Judul Tab
    protected static ?string $title = 'Soal Ujian';
    protected static ?string $modelLabel = 'Soal';

    public function form(Schema $form): Schema
    {
        // Reuse the form from QuestionResource for consistency
        return QuestionResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('examType.name')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn($record) => match ($record->examType?->evaluation_method) {
                        'correct_wrong' => 'info',
                        'weighted' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('question_text')
                    ->label('Soal')
                    ->html()
                    ->formatStateUsing(function ($state) {
                        return Str::limit(strip_tags((string)$state), 80);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('generate_random')
                    ->label('Generate Acak')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('primary')
                    ->form(function () {
                        /** @var ExamPackage $examPackage */
                        $examPackage = $this->getOwnerRecord();
                        $examType = $examPackage->examType;

                        if (! $examType) {
                            return [];
                        }

                        // IDs yang sudah ada di paket ini, supaya tidak dipilih lagi
                        $existingIds = $examPackage->questions()->pluck('questions.id')->toArray();

                        // Base Query: Tipe sama, belum ada di paket ini
                        $queryBase = Question::query()
                            ->where('exam_type_id', $examType->id)
                            ->whereNotIn('id', $existingIds);

                        if ($examType->isWeighted()) {
                            $available = $queryBase->count();
                            return [
                                \Filament\Forms\Components\TextInput::make('total_count')
                                    ->label('Jumlah Soal')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue($available)
                                    ->helperText("Tersedia: {$available} soal (belum terpilih)"),
                            ];
                        }

                        if ($examType->isCorrectWrong()) {
                            // Hitung ketersediaan per kategori
                            $countEasy = (clone $queryBase)->where('category', 'easy')->count();
                            $countMedium = (clone $queryBase)->where('category', 'medium')->count();
                            $countHard = (clone $queryBase)->where('category', 'hard')->count();

                            return [
                                Grid::make(3)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('easy_count')
                                            ->label('Mudah')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue($countEasy)
                                            ->helperText("Tersedia: {$countEasy}"),
                                        \Filament\Forms\Components\TextInput::make('medium_count')
                                            ->label('Sedang')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue($countMedium)
                                            ->helperText("Tersedia: {$countMedium}"),
                                        \Filament\Forms\Components\TextInput::make('hard_count')
                                            ->label('Sulit')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue($countHard)
                                            ->helperText("Tersedia: {$countHard}"),
                                    ]),
                            ];
                        }

                        return [];
                    })
                    ->action(function (array $data) {
                        /** @var ExamPackage $examPackage */
                        $examPackage = $this->getOwnerRecord();
                        $examType = $examPackage->examType;
                        $existingIds = $examPackage->questions()->pluck('questions.id')->toArray();

                        $idsToAttach = collect();

                        $queryBase = Question::query()
                            ->where('exam_type_id', $examType->id)
                            ->whereNotIn('id', $existingIds);

                        if ($examType->isWeighted()) {
                            $count = (int) ($data['total_count'] ?? 0);
                            if ($count > 0) {
                                $ids = $queryBase->inRandomOrder()->limit($count)->pluck('id');
                                $idsToAttach = $idsToAttach->merge($ids);
                            }
                        } elseif ($examType->isCorrectWrong()) {
                            // Easy
                            $easy = (int) ($data['easy_count'] ?? 0);
                            if ($easy > 0) {
                                $ids = (clone $queryBase)->where('category', 'easy')->inRandomOrder()->limit($easy)->pluck('id');
                                $idsToAttach = $idsToAttach->merge($ids);
                            }
                            // Medium
                            $medium = (int) ($data['medium_count'] ?? 0);
                            if ($medium > 0) {
                                $ids = (clone $queryBase)->where('category', 'medium')->inRandomOrder()->limit($medium)->pluck('id');
                                $idsToAttach = $idsToAttach->merge($ids);
                            }
                            // Hard
                            $hard = (int) ($data['hard_count'] ?? 0);
                            if ($hard > 0) {
                                $ids = (clone $queryBase)->where('category', 'hard')->inRandomOrder()->limit($hard)->pluck('id');
                                $idsToAttach = $idsToAttach->merge($ids);
                            }
                        }

                        if ($idsToAttach->isNotEmpty()) {
                            $examPackage->questions()->attach($idsToAttach->toArray());

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil')
                                ->body('Berhasil menambahkan ' . $idsToAttach->count() . ' soal secara acak.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada perubahan')
                                ->body('Tidak ada soal yang ditambahkan (input 0 atau tidak ada stok).')
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Pertanyaan')
                    ->modalContent(fn($record) => view('filament.modals.question-detail', [
                        'record' => $record,
                        'manager' => $this,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                DetachAction::make()
                    ->label('Hapus Soal')
                    ->modalHeading('Hapus Soal dari Paket')
                    ->modalDescription('Apakah Anda yakin ingin menghapus soal ini dari paket ujian? Soal ini tidak akan terhapus dari Bank Soal.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label('Hapus Soal Terpilih')
                    ->modalHeading('Hapus Soal Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus soal-soal yang dipilih dari paket ujian ini?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->reorderable('sort_order');
    }

    /**
     * Format the scoring configuration for display
     */
    public function formatScoringConfig(Question $question): string
    {
        // Delegate to shared helper to keep formatting consistent across places
        return \App\Helpers\ScoringConfigFormatter::format($question);
    }
}
