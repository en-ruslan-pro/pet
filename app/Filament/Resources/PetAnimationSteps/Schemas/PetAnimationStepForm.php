<?php

namespace App\Filament\Resources\PetAnimationSteps\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PetAnimationStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Системный ключ шага')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Название шага')
                    ->required()
                    ->maxLength(100),
            ]);
    }
}
