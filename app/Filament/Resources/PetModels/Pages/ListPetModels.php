<?php

namespace App\Filament\Resources\PetModels\Pages;

use App\Filament\Resources\PetModels\PetModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPetModels extends ListRecords
{
    protected static string $resource = PetModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
