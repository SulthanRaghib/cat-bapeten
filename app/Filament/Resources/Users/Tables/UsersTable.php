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
                    ->label(__('User Name'))
                    ->description(fn($record): string => $record->email ?? '—')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('nip')
                    ->label(__('NIP'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('NIP Copied!'))
                    ->copyMessageDuration(2000),

                TextColumn::make('role')
                    ->label(__('Role'))
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
                            'observer'    => __('Exam Observer'),
                            'user'        => __('Exam Participant'),
                        ];

                        return $labels[$state] ?? ucwords(str_replace('_', ' ', $state));
                    }),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('Email Copied!'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('Registered Since'))
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('Role'))
                    ->options(static function (): array {
                        $labels = [
                            'super_admin' => 'Super Admin',
                            'admin'       => 'Administrator',
                            'observer'    => __('Exam Observer'),
                            'user'        => __('Exam Participant'),
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
                        ->label(__('Edit User'))
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->label(__('Delete User'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Delete User?'))
                        ->modalDescription(__('Are you sure you want to delete this user? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])
                    ->label(__('Action Group'))
                    ->button()
                    ->size(Size::Small)
                    ->outlined(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Delete Selected Users'))
                        ->modalHeading(__('Delete Selected Users'))
                        ->modalDescription(__('Are you sure you want to delete the selected users? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ])
            ->emptyStateHeading(__('No users yet'))
            ->emptyStateDescription(__('Add new users to provide access to the system.'))
            ->emptyStateIcon('heroicon-o-users');
    }
}
