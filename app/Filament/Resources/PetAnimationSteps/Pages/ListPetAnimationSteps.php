<?php

namespace App\Filament\Resources\PetAnimationSteps\Pages;

use App\Filament\Resources\PetAnimationSteps\PetAnimationStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPetAnimationSteps extends ListRecords
{
    protected static string $resource = PetAnimationStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
