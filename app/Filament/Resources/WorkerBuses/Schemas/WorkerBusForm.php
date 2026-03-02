<?php

namespace App\Filament\Resources\WorkerBuses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkerBusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required(),
                Select::make('type')
                    ->label('النوع')
                    ->options([
                        'driver' => 'سائق',
                        'delegate' => 'مندوب',
                    ])
                    ->required()
            ]);
    }
}
