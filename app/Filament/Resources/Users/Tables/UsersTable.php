<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

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
                    ->icon(static function (string $state): string {
                        return match ($state) {
                            'super_admin' => 'heroicon-m-shield-exclamation',
                            'admin'       => 'heroicon-m-shield-check',
                            'observer'    => 'heroicon-m-eye',
                            'user'        => 'heroicon-m-user',
                            default       => 'heroicon-m-adjustments-horizontal',
                        };
                    })
                    ->color(static function (string $state): string {
                        return match ($state) {
                            'super_admin' => 'warning',
                            'admin'       => 'danger',
                            'observer'    => 'success',
                            'user'        => 'info',
                            default       => 'gray',
                        };
                    })
                    ->formatStateUsing(static function (string $state): string {
                        $labels = [
                            'super_admin' => 'Super Admin',
                            'admin'       => 'Administrator',
                            'observer'    => 'Pengawas Ujian',
                            'user'        => 'Peserta Ujian',
                        ];

                        return $labels[$state] ?? ucwords(str_replace('_', ' ', $state));
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
                    ->options(static function (): array {
                        $labels = [
                            'super_admin' => 'Super Admin',
                            'admin'       => 'Administrator',
                            'observer'    => 'Pengawas Ujian',
                            'user'        => 'Peserta Ujian',
                        ];

                        return Role::query()
                            ->where('guard_name', 'web')
                            ->pluck('name')
                            ->mapWithKeys(fn(string $name): array => [
                                $name => $labels[$name] ?? ucwords(str_replace('_', ' ', $name)),
                            ])
                            ->toArray();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Edit Pengguna')
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->label('Hapus Pengguna')
                        ->icon('heroicon-m-trash')
                        ->modalHeading('Hapus Pengguna?')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
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
