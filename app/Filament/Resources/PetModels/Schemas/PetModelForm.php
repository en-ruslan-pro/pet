<?php

namespace App\Filament\Resources\PetModels\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PetModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Модель')
                    ->schema([
                        Select::make('pet_type_id')
                            ->label('Тип питомца')
                            ->relationship('type', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('key')
                            ->label('Системный ключ')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('asset_path')
                            ->label('Путь к GLB')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Например: /models/kaykit-adventurers/Knight.glb'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Внутренние шаги и варианты клипов')
                    ->description('Один шаг модели может использовать несколько точных имён клипов GLB. Вес определяет вероятность выбора варианта.')
                    ->schema([
                        Repeater::make('animationSteps')
                            ->relationship()
                            ->schema([
                                Select::make('pet_animation_step_id')
                                    ->label('Внутренний шаг')
                                    ->relationship('animationStep', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Toggle::make('is_available')
                                    ->label('Активен')
                                    ->default(true),
                                Repeater::make('clips')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('clip_name')
                                            ->label('Имя клипа GLB')
                                            ->required()
                                            ->maxLength(150),
                                        TextInput::make('weight')
                                            ->label('Вес')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->required(),
                                        TextInput::make('playback_rate')
                                            ->label('Скорость')
                                            ->numeric()
                                            ->minValue(0.1)
                                            ->maxValue(9.99)
                                            ->default(1)
                                            ->required(),
                                        Toggle::make('is_looping')
                                            ->label('Зациклить'),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->addActionLabel('Добавить вариант клипа')
                                    ->defaultItems(0),
                            ])
                            ->columns(2)
                            ->addActionLabel('Добавить внутренний шаг')
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
                Section::make('Игровые действия')
                    ->description('Действие выполняет активные внутренние шаги сверху вниз. Перетащите шаги в нужный порядок.')
                    ->schema([
                        Repeater::make('petModelActions')
                            ->relationship()
                            ->schema([
                                Select::make('pet_action_id')
                                    ->label('Игровое действие')
                                    ->relationship('petAction', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Toggle::make('is_available')
                                    ->label('Активно')
                                    ->default(true),
                                TextInput::make('interaction_points.room_item_key')
                                    ->label('Цель в комнате')
                                    ->maxLength(100)
                                    ->helperText('Например: food_bowl, sofa или toy_mouse.'),
                                TextInput::make('execution_configuration.duration_min_seconds')
                                    ->label('Мин. длительность, сек.')
                                    ->numeric()
                                    ->minValue(1),
                                TextInput::make('execution_configuration.duration_max_seconds')
                                    ->label('Макс. длительность, сек.')
                                    ->numeric()
                                    ->minValue(1),
                                Repeater::make('steps')
                                    ->relationship()
                                    ->orderColumn('position')
                                    ->schema([
                                        Select::make('pet_animation_step_id')
                                            ->label('Внутренний шаг')
                                            ->relationship('animationStep', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        Toggle::make('is_available')
                                            ->label('Активен')
                                            ->default(true),
                                        TextInput::make('duration_seconds')
                                            ->label('Длительность, сек.')
                                            ->numeric()
                                            ->minValue(1)
                                            ->nullable(),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->addActionLabel('Добавить шаг')
                                    ->defaultItems(0),
                            ])
                            ->columns(2)
                            ->addActionLabel('Добавить игровое действие')
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
