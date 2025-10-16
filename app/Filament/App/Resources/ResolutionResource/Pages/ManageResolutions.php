<?php

namespace App\Filament\App\Resources\ResolutionResource\Pages;

use App\Filament\App\Resources\ResolutionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageResolutions extends ManageRecords
{
    protected static string $resource = ResolutionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
