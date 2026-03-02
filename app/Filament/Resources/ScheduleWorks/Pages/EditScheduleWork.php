<?php

namespace App\Filament\Resources\ScheduleWorks\Pages;

use App\Filament\Resources\ScheduleWorks\ScheduleWorkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScheduleWork extends EditRecord
{
    protected static string $resource = ScheduleWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
