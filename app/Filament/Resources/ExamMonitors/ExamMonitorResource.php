<?php

namespace App\Filament\Resources\ExamMonitors;

use App\Filament\Resources\ExamMonitors\Pages\ListExamMonitors;
use App\Filament\Resources\ExamMonitors\Tables\ExamMonitorsTable;
use App\Models\ExamSession;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ExamMonitorResource extends Resource
{
    protected static ?string $model = ExamSession::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    protected static ?string $modelLabel = 'Monitoring Ujian';
    protected static ?string $pluralModelLabel = 'Monitoring Ujian';
    protected static ?string $navigationLabel = 'Monitoring Ujian';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan & Hasil';

    /**
     * Akses halaman Monitoring Ujian:
     * - super_admin : Gate::before bypass → true otomatis
     * - admin       : punya ViewAny:ExamSession → true
     * - observer    : punya ViewAny:ExamSession → true
     *                 (ExamResultResource di-block via canViewAny override-nya sendiri)
     */
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can('ViewAny:ExamSession');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('status', ['ongoing', 'paused']);
    }

    public static function table(Table $table): Table
    {
        return ExamMonitorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamMonitors::route('/'),
        ];
    }
}
