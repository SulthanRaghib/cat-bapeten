<?php

declare(strict_types=1);

namespace App\Filament\Resources\SelectionStageTypes\Tables;

use App\Models\SelectionStageType;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SelectionStageTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                // Sort handle hint
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50)
                    ->alignCenter(),

                // Icon preview
                IconColumn::make('icon')
                    ->label(__('Icon'))
                    ->icon(function (?string $state): string {
                        if (! $state) {
                            return 'heroicon-o-tag';
                        }

                        try {
                            app(\BladeUI\Icons\Factory::class)->svg($state);
                            return $state;
                        } catch (\Throwable $e) {
                            return 'heroicon-o-tag';
                        }
                    })
                    ->color('primary')
                    ->alignCenter()
                    ->width(60),

                TextColumn::make('name')
                    ->label(__('Stage Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('description')
                    ->label(__('Description'))
                    ->placeholder('—')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label(__('Active'))
                    ->alignCenter()
                    ->width(80),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Add Stage Type'))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label(__('Edit Stage'))
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->label(__('Delete Stage'))
                        ->icon('heroicon-m-trash')
                        ->modalHeading(__('Delete Selection Stage Type?'))
                        ->modalDescription(__('Are you sure you want to delete this stage type? This action cannot be undone.'))
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
                        ->label(__('Delete Selected'))
                        ->modalHeading(__('Delete Selected Stage Types?'))
                        ->modalDescription(__('Are you sure you want to delete the selected stage types? This action cannot be undone.'))
                        ->modalSubmitActionLabel(__('Yes, Delete')),
                ])->label(__('Bulk Actions')),
            ])
            ->emptyStateHeading(__('No selection stage types yet'))
            ->emptyStateDescription(__('Add selection stage types used in the recruitment process, such as Interview, FGD, or Presentation.'))
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}
