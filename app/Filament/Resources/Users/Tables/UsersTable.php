<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->description(fn($record): string => $record->email ?? '—')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('NIP disalin!')
                    ->copyMessageDuration(2000),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->icon(fn(string $state): string => match ($state) {
                        'admin' => 'heroicon-m-shield-check',
                        'user'  => 'heroicon-m-user',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user'  => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'user'  => 'Pengguna',
                        default => $state,
                    }),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email disalin!')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Terdaftar Sejak')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Administrator',
                        'user'  => 'Pengguna',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit Pengguna'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Pengguna Terpilih')
                        ->modalHeading('Hapus Pengguna Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengguna yang dipilih ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])->label('Tindakan Massal'),
            ])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Tambahkan pengguna baru untuk memberikan akses ke sistem.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
