<?php

namespace App\Filament\Resources\PetActions\Pages;

use App\Filament\Resources\PetActions\PetActionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPetActions extends ListRecords
{
    protected static string $resource = PetActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
