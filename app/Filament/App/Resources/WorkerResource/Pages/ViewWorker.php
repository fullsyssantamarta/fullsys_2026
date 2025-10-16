<?php

namespace App\Filament\App\Resources\WorkerResource\Pages;

use App\Filament\App\Resources\WorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorker extends ViewRecord
{
    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
