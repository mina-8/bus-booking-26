<?php

namespace App\Filament\Resources\ScheduleWorks\Pages;

use App\Filament\Resources\ScheduleWorks\ScheduleWorkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListScheduleWorks extends ListRecords
{
    protected static string $resource = ScheduleWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
