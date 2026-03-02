<?php

namespace App\Filament\Resources\WorkerBuses\Pages;

use App\Filament\Resources\WorkerBuses\WorkerBusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkerBuses extends ListRecords
{
    protected static string $resource = WorkerBusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
