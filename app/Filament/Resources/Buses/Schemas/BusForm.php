<?php

namespace App\Filament\Resources\Buses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Schemas\Schema;

class BusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Bus Name')
                    ->required(),
                TextInput::make('plate_number')
                    ->label('Plate Number')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('seats')
                    ->label('Number of Seats')
                    ->numeric()
                    ->maxValue(14)
                    ->required()
                    ->suffixAction(
                        Action::make('setDefault')
                            ->icon('heroicon-m-check')
                            ->action(function ($set) {
                                $set('seats', 14);
                            })
                    )
                    ->extraInputAttributes([
                        'style' => '-moz-appearance: textfield;',
                        'class' => '[&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none',
                    ]),
            ]);
    }
}
