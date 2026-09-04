<?php

namespace App\Filament\Resources\PetModels;

use App\Filament\Resources\PetModels\Pages\CreatePetModel;
use App\Filament\Resources\PetModels\Pages\EditPetModel;
use App\Filament\Resources\PetModels\Pages\ListPetModels;
use App\Filament\Resources\PetModels\Schemas\PetModelForm;
use App\Filament\Resources\PetModels\Tables\PetModelsTable;
use App\Models\PetModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PetModelResource extends Resource
{
    protected static ?string $model = PetModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = '3D-модель';

    protected static ?string $pluralModelLabel = '3D-модели';

    protected static ?string $navigationLabel = '3D-модели';

    public static function form(Schema $schema): Schema
    {
        return PetModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PetModelsTable::configure($table);
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
            'index' => ListPetModels::route('/'),
            'create' => CreatePetModel::route('/create'),
            'edit' => EditPetModel::route('/{record}/edit'),
        ];
    }
}
