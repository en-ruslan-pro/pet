<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(100),
                TextInput::make('default_name')
                    ->label('Имя по умолчанию')
                    ->required()
                    ->maxLength(30),
                Select::make('pet_model_id')
                    ->label('3D-модель')
                    ->relationship('petModel', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
