<?php

namespace App\Filament\Resources\PetActions\Pages;

use App\Filament\Resources\PetActions\PetActionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPetAction extends EditRecord
{
    protected static string $resource = PetActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
