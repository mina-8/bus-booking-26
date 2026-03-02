<?php

namespace App\Filament\Resources\ScheduleWorks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class ScheduleWorkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date_from')
                    ->label('Date')
                    ->native(false)
                    ->required(),
                DatePicker::make('date_to')
                    ->label('To Date')
                    ->native(false)
                    ->after('date_from')
                    ->required()
            ]);
    }
}
