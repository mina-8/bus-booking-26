<?php

namespace App\Filament\Resources\Trips\Schemas;

use App\Models\City;
use App\Models\Trip;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_one_id')
                    ->label('From City')
                    ->options(function (Get $get) {
                        $excludedCityId = $get('city_two_id');
                        return City::query()
                            ->when($excludedCityId, fn($query) => $query->where('id', '!=', $excludedCityId))
                            ->pluck('name', 'id');
                    })
                    ->live()
                    ->searchable()
                    ->required(),
                Select::make('city_two_id')
                    ->label('To City')
                    ->options(function (Get $get) {
                        $excludedCityId = $get('city_one_id');
                        return City::query()
                            ->when($excludedCityId, fn($query) => $query->where('id', '!=', $excludedCityId))
                            ->pluck('name', 'id');
                    })
                    ->live()
                    ->searchable()
                    ->required()
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $cityOneId = $get('city_one_id');
                            $recordId = $get('../../id'); // Get current record ID if editing

                            if ($cityOneId && $value) {
                                $query = Trip::where(function ($query) use ($cityOneId, $value) {
                                    $query->where('city_one_id', $cityOneId)
                                          ->where('city_two_id', $value);
                                })
                                ->orWhere(function ($query) use ($cityOneId, $value) {
                                    $query->where('city_one_id', $value)
                                          ->where('city_two_id', $cityOneId);
                                });

                                // If editing, exclude current record
                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }

                                if ($query->exists()) {
                                    $fail('A trip with this route already exists.');
                                }
                            }
                        },
                    ]),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                TextInput::make('round_trip_price')
                    ->label('Round Trip Price')
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}
