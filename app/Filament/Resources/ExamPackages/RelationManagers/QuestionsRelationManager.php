<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\Question;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $form): Schema
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Detail Soal')
                    ->description('Kelola isi pertanyaan serta opsi jawaban secara fleksibel.')
                    ->schema([
                        RichEditor::make('question_text')
                            ->label('Pertanyaan')
                            ->placeholder('Tulis isi pertanyaan di sini')
                            ->required()
                            ->columnSpanFull(),

                        Repeater::make('options_data')
                            ->label('Pilihan Jawaban')
                            ->addActionLabel('Tambah Pilihan')
                            ->helperText('Kode A, B, C, dst akan dibuat otomatis sesuai urutan opsi.')
                            ->minItems(2)
                            ->maxItems(10)
                            ->reorderable()
                            ->columns(12)
                            ->schema([
                                TextInput::make('teks')
                                    ->label('Teks Pilihan')
                                    ->placeholder('Masukkan teks pilihan')
                                    ->required()
                                    ->columnSpan(9),

                                TextInput::make('bobot')
                                    ->label('Bobot Nilai')
                                    ->placeholder('Masukkan bobot nilai')
                                    ->numeric()
                                    ->visible(fn(): bool => $this->isStructural())
                                    ->required(fn(): bool => $this->isStructural())
                                    ->columnSpan(3),
                            ])
                            ->default(fn(): array => $this->getDefaultOptionsData())
                            ->columnSpanFull(),

                        Select::make('scoring_config.correct')
                            ->label('Kunci Jawaban Benar')
                            ->placeholder('Pilih jawaban yang benar')
                            ->options(fn(Get $get): array => $this->generateOptionChoices($get))
                            ->required(fn(): bool => $this->isTechnical())
                            ->visible(fn(): bool => $this->isTechnical())
                            ->searchable()
                            ->columnSpanFull(),
                    ])
                    ->columns(12),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question_text')
                    ->label('Soal')
                    ->html()
                    ->limit(50),

                TextColumn::make('scoring_config')
                    ->label('Konfigurasi Penilaian')
                    ->formatStateUsing(fn(Question $record): string => $this->formatScoringConfig($record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Soal Baru')
                    ->mutateFormDataUsing(fn(array $data): array => $this->normalisasiScoringConfig($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->mutateRecordDataUsing(fn(array $data, Question $record): array => $this->hydrateOptionsData([
                        ...$data,
                        'options' => $record->options,
                        'scoring_config' => $record->scoring_config,
                    ]))
                    ->mutateFormDataUsing(fn(array $data): array => $this->normalisasiScoringConfig($data)),
                DeleteAction::make()
                    ->label('Hapus'),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->hydrateOptionsData($data);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalisasiScoringConfig($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalisasiScoringConfig($data);
    }

    private function normalisasiScoringConfig(array $data): array
    {
        if (! array_key_exists('options_data', $data)) {
            return $data;
        }

        $optionRows = collect($data['options_data'] ?? [])
            ->map(fn(array $item): array => [
                'teks' => trim((string) ($item['teks'] ?? '')),
                'bobot' => $item['bobot'] ?? null,
            ])
            ->filter(fn(array $item): bool => filled($item['teks']))
            ->values();

        if ($optionRows->isEmpty()) {
            $optionRows = collect($this->getDefaultOptionsData());
        }

        $options = [];
        $scoring = [];

        foreach ($optionRows as $index => $item) {
            $kode = $this->alphabetFromIndex($index);
            $options[$kode] = $item['teks'];

            if ($this->isStructural()) {
                $nilai = $item['bobot'] ?? 0;
                $scoring[$kode] = is_numeric($nilai) ? (float) $nilai : 0.0;
            }
        }

        $data['options'] = $options;

        if ($this->isTechnical()) {
            $correct = Str::upper((string) ($data['scoring_config']['correct'] ?? ''));

            if (! array_key_exists($correct, $options)) {
                $correct = array_key_first($options);
            }

            $data['scoring_config'] = [
                'correct' => $correct,
            ];
        } else {
            $data['scoring_config'] = $scoring;
        }

        unset($data['options_data']);

        return $data;
    }

    private function hydrateOptionsData(array $data): array
    {
        $tipe = $this->getExamType();
        $options = $data['options'] ?? [];
        $scoring = $data['scoring_config'] ?? [];

        if (! is_array($options) || empty($options)) {
            $data['options_data'] = $this->getDefaultOptionsData();

            if ($tipe === 'technical') {
                $data['scoring_config'] = [
                    'correct' => 'A',
                ];
            }

            return $data;
        }

        $data['options_data'] = collect($options)
            ->map(function ($teks, $kode) use ($tipe, $scoring): array {
                $row = [
                    'teks' => $teks,
                ];

                if ($tipe === 'structural') {
                    $row['bobot'] = $scoring[$kode] ?? null;
                }

                return $row;
            })
            ->values()
            ->all();

        if ($tipe === 'technical') {
            $data['scoring_config'] = [
                'correct' => $scoring['correct'] ?? array_key_first($options),
            ];
        }

        return $data;
    }

    private function formatScoringConfig(Question $record): string
    {
        $tipe = $record->examPackage?->type ?? 'technical';

        if ($tipe === 'technical') {
            $kunci = $record->scoring_config['correct'] ?? null;

            return 'Kunci: ' . ($kunci ?: '-');
        }

        $bagian = collect($record->scoring_config ?? [])
            ->map(fn($nilai, $kode): string => sprintf('%s=%s', $kode, $nilai))
            ->implode(', ');

        return 'Bobot: ' . ($bagian !== '' ? $bagian : '-');
    }

    private function generateOptionChoices(Get $get): array
    {
        $optionRows = collect($get('options_data') ?? [])
            ->filter(fn($item): bool => filled($item['teks'] ?? null))
            ->values();

        $choices = [];

        foreach ($optionRows as $index => $item) {
            $kode = $this->alphabetFromIndex($index);
            $choices[$kode] = sprintf('%s - %s', $kode, Str::limit((string) $item['teks'], 40));
        }

        return $choices;
    }

    private function getDefaultOptionsData(): array
    {
        return [
            ['teks' => ''],
            ['teks' => ''],
        ];
    }

    private function alphabetFromIndex(int $index): string
    {
        return chr(ord('A') + $index);
    }

    private function isTechnical(): bool
    {
        return $this->getExamType() === 'technical';
    }

    private function isStructural(): bool
    {
        return $this->getExamType() === 'structural';
    }

    private function getExamType(): string
    {
        return $this->getOwnerRecord()?->type ?? 'technical';
    }
}
