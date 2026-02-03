<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

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
                    ->color('success')
                    ->form(function () {
                        /** @var \App\Models\ExamPackage $examPackage */
                        $examPackage = $this->getOwnerRecord();
                        $type = $examPackage->type;

                        // IDs yang sudah ada di paket ini, supaya tidak dipilih lagi
                        $existingIds = $examPackage->questions()->pluck('questions.id')->toArray();

                        // Base Query: Tipe sama, belum ada di paket ini
                        $queryBase = Question::query()
                            ->where('type', $type)
                            ->whereNotIn('id', $existingIds);

                        if ($type === 'structural') {
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

                        if ($type === 'technical') {
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
                        /** @var \App\Models\ExamPackage $examPackage */
                        $examPackage = $this->getOwnerRecord();
                        $type = $examPackage->type;
                        $existingIds = $examPackage->questions()->pluck('questions.id')->toArray();

                        $idsToAttach = collect();

                        $queryBase = Question::query()
                            ->where('type', $type)
                            ->whereNotIn('id', $existingIds);

                        if ($type === 'structural') {
                            $count = (int) ($data['total_count'] ?? 0);
                            if ($count > 0) {
                                $ids = $queryBase->inRandomOrder()->limit($count)->pluck('id');
                                $idsToAttach = $idsToAttach->merge($ids);
                            }
                        } elseif ($type === 'technical') {
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
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Pertanyaan')
                    ->modalContent(fn($record) => view('filament.modals.question-detail', [
                        'record' => $record,
                        // Pass a simplified mock or remove logic if formatting complex scoring requires manager
                        'manager' => $this,
                    ])),
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
        // Try to handle legacy structure or specific structure logic
        if ($question->type === 'structural') {
            // Often hidden in options array for structural questions or scoring_config
            $score = 0;
            $options = $question->options;
            if (is_array($options)) {
                foreach ($options as $opt) {
                    if (isset($opt['score'])) {
                        $score = $opt['score'];
                        break;
                    }
                }
            }
            return '<strong>Bobot Nilai:</strong> ' . $score . ' Poin';
        }

        // Technical usually implies one correct answer worth 5 points (default system) or similar
        return '<strong>Tipe Teknis:</strong> Benar/Salah';
    }
}
