<?php

namespace App\Filament\Resources\Characters\Schemas;

use App\Models\Character;
use App\Models\PetModel;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $set('enabled_animation_clips', $state === null
                            ? []
                            : PetModel::query()->find($state)?->animationClipNames() ?? []);
                    })
                    ->required(),
                CheckboxList::make('enabled_animation_clips')
                    ->label('Включённые анимации')
                    ->options(function (Get $get): array {
                        $petModel = PetModel::query()->find($get('pet_model_id'));

                        if (! $petModel instanceof PetModel) {
                            return [];
                        }

                        $clips = $petModel->animationClipNames();

                        return array_combine($clips, $clips) ?: [];
                    })
                    ->afterStateHydrated(function (CheckboxList $component, ?Character $record, ?array $state): void {
                        if ($state === null && $record !== null) {
                            $component->state($record->petModel->animationClipNames());
                        }
                    })
                    ->bulkToggleable()
                    ->columns(2)
                    ->helperText('При смене 3D-модели по умолчанию включаются все её анимации.'),
            ]);
    }
}
