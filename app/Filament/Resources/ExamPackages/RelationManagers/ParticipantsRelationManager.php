<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamPackages\RelationManagers;

use App\Models\ExamPackage;
use App\Models\ExamParticipant;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
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
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP / ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.token')
                    ->label('Access Token')
                    ->copyable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->state(fn(Model $record) => $record->pivot->is_active)
                    ->updateStateUsing(function (Model $record, $state): Model {
                        $record->pivot->update(['is_active' => $state]);
                        return $record;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->multiple()
                    ->preloadRecordSelect()
                    ->form(fn(AttachAction $action) => [
                        $action->getRecordSelect()
                            ->searchable()
                            ->placeholder('Pilih peserta'),
                    ])
                    ->using(function (BelongsToMany $relationship, array $data): void {
                        $owner = $relationship->getParent();

                        if (! $owner instanceof ExamPackage) {
                            return;
                        }

                        $userIds = $data['recordId'] ?? [];

                        if (! is_array($userIds)) {
                            $userIds = [$userIds];
                        }

                        foreach ($userIds as $userId) {
                            ExamParticipant::firstOrCreate(
                                [
                                    'exam_package_id' => $owner->getKey(),
                                    'user_id' => $userId,
                                ],
                                [
                                    'is_active' => true,
                                ],
                            );
                        }
                    })
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
