<?php

namespace App\Filament\Resources\WorkerBuses\Pages;

use App\Filament\Resources\WorkerBuses\WorkerBusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkerBus extends EditRecord
{
    protected static string $resource = WorkerBusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
