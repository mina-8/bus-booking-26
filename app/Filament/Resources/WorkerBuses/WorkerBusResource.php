<?php

namespace App\Filament\Resources\WorkerBuses;

use App\Filament\Resources\WorkerBuses\Pages\CreateWorkerBus;
use App\Filament\Resources\WorkerBuses\Pages\EditWorkerBus;
use App\Filament\Resources\WorkerBuses\Pages\ListWorkerBuses;
use App\Filament\Resources\WorkerBuses\Schemas\WorkerBusForm;
use App\Filament\Resources\WorkerBuses\Tables\WorkerBusesTable;
use App\Models\WorkerBus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkerBusResource extends Resource
{
    protected static ?string $model = WorkerBus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $navigationLabel = 'العاملين';

    protected static ?string $modelLabel = 'عامل حافلة';

    protected static ?string $pluralModelLabel = 'عمال الحافلات';

    public static function form(Schema $schema): Schema
    {
        return WorkerBusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkerBusesTable::configure($table);
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
            'index' => ListWorkerBuses::route('/'),
            'create' => CreateWorkerBus::route('/create'),
            'edit' => EditWorkerBus::route('/{record}/edit'),
        ];
    }
}
