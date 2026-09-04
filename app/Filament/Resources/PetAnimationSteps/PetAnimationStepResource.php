<?php

namespace App\Filament\Resources\PetAnimationSteps;

use App\Filament\Resources\PetAnimationSteps\Pages\CreatePetAnimationStep;
use App\Filament\Resources\PetAnimationSteps\Pages\EditPetAnimationStep;
use App\Filament\Resources\PetAnimationSteps\Pages\ListPetAnimationSteps;
use App\Filament\Resources\PetAnimationSteps\Schemas\PetAnimationStepForm;
use App\Filament\Resources\PetAnimationSteps\Tables\PetAnimationStepsTable;
use App\Models\PetAnimationStep;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PetAnimationStepResource extends Resource
{
    protected static ?string $model = PetAnimationStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'внутренний шаг';

    protected static ?string $pluralModelLabel = 'внутренние шаги';

    protected static ?string $navigationLabel = 'Внутренние шаги';

    public static function form(Schema $schema): Schema
    {
        return PetAnimationStepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PetAnimationStepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPetAnimationSteps::route('/'),
            'create' => CreatePetAnimationStep::route('/create'),
            'edit' => EditPetAnimationStep::route('/{record}/edit'),
        ];
    }
}
