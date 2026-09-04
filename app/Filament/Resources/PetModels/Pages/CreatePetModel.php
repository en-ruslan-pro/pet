<?php

namespace App\Filament\Resources\PetModels\Pages;

use App\Filament\Resources\PetModels\PetModelResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePetModel extends CreateRecord
{
    protected static string $resource = PetModelResource::class;
}
