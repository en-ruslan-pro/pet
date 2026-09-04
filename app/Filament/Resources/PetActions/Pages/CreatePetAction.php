<?php

namespace App\Filament\Resources\PetActions\Pages;

use App\Filament\Resources\PetActions\PetActionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePetAction extends CreateRecord
{
    protected static string $resource = PetActionResource::class;
}
