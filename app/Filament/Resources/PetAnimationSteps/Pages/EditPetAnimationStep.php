<?php

namespace App\Filament\Resources\PetAnimationSteps\Pages;

use App\Filament\Resources\PetAnimationSteps\PetAnimationStepResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPetAnimationStep extends EditRecord
{
    protected static string $resource = PetAnimationStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
