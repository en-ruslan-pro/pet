<?php

namespace App\Filament\Resources\PetModels\Pages;

use App\Filament\Resources\PetModels\PetModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPetModel extends EditRecord
{
    protected static string $resource = PetModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
