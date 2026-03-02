<?php

namespace App\Filament\Resources\ScheduleWorks;

use App\Filament\Resources\ScheduleWorks\Pages\CreateScheduleWork;
use App\Filament\Resources\ScheduleWorks\Pages\EditScheduleWork;
use App\Filament\Resources\ScheduleWorks\Pages\ListScheduleWorks;
use App\Filament\Resources\ScheduleWorks\Schemas\ScheduleWorkForm;
use App\Filament\Resources\ScheduleWorks\Tables\ScheduleWorksTable;
use App\Models\ScheduleWork;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScheduleWorkResource extends Resource
{
    protected static ?string $model = ScheduleWork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'جداول العمل';

    protected static ?string $modelLabel = 'جدول عمل';

    protected static ?string $pluralModelLabel = 'جداول العمل';

    public static function form(Schema $schema): Schema
    {
        return ScheduleWorkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduleWorksTable::configure($table);
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
            'index' => ListScheduleWorks::route('/'),
            'create' => CreateScheduleWork::route('/create'),
            'edit' => EditScheduleWork::route('/{record}/edit'),
        ];
    }
}
