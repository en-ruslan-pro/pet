<?php

namespace App\Filament\Resources\PetActions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PetActionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Системный ключ')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(100),
                Section::make('Автономное поведение')
                    ->schema([
                        Toggle::make('configuration.is_autonomous')
                            ->label('Доступно автономному поведению')
                            ->default(true),
                        Toggle::make('configuration.is_controller_available')
                            ->label('Доступно с контроллера'),
                        TextInput::make('configuration.autonomous_weight')
                            ->label('Вес автономного выбора')
                            ->numeric()
                            ->minValue(0)
                            ->default(1),
                        TextInput::make('configuration.duration_min_seconds')
                            ->label('Минимальная длительность, сек.')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('configuration.duration_max_seconds')
                            ->label('Максимальная длительность, сек.')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(3),
                Section::make('Изменение потребностей после действия')
                    ->schema([
                        TextInput::make('configuration.need_effects.hunger')
                            ->label('Голод')
                            ->numeric()
                            ->default(0),
                        TextInput::make('configuration.need_effects.energy')
                            ->label('Энергия')
                            ->numeric()
                            ->default(0),
                        TextInput::make('configuration.need_effects.happiness')
                            ->label('Счастье')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }
}
