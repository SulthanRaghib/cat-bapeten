<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    // Translasi Judul Tab
    protected static ?string $title = 'Peserta Ujian';
    protected static ?string $modelLabel = 'Peserta';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('token')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('token')
                    ->label('Token Akses')
                    ->weight('bold')
                    ->color(Color::Amber)
                    ->copyable()
                    ->copyMessage('Token disalin!')
                    ->description('Bagikan token ini ke peserta'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Peserta')
                    ->color(Color::Amber)
                    ->modalHeading('Pilih Peserta Ujian')
                    ->modalSubmitActionLabel('Tambahkan')
                    ->preloadRecordSelect()
                    ->multiple() // Bisa pilih banyak sekaligus
                    ->recordSelectSearchColumns(['name', 'nip']),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Hapus Peserta'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Hapus Peserta Terpilih'),
                ]),
            ]);
    }
}
