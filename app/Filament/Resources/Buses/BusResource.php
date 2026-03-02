<?php

namespace App\Filament\Resources\Buses;

use App\Filament\Resources\Buses\Pages\CreateBus;
use App\Filament\Resources\Buses\Pages\EditBus;
use App\Filament\Resources\Buses\Pages\ListBuses;
use App\Filament\Resources\Buses\RelationManagers\WorkerBusesRelationManager;
use App\Filament\Resources\Buses\Schemas\BusForm;
use App\Filament\Resources\Buses\Tables\BusesTable;
use App\Models\Bus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $navigationLabel = 'الحافلات';

    protected static ?string $modelLabel = 'حافلة';

    protected static ?string $pluralModelLabel = 'الحافلات';

    public static function form(Schema $schema): Schema
    {
        return BusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            WorkerBusesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBuses::route('/'),
            'create' => CreateBus::route('/create'),
            'edit' => EditBus::route('/{record}/edit'),
        ];
    }
}
