<?php

namespace App\Filament\Resources\PetActions;

use App\Filament\Resources\PetActions\Pages\CreatePetAction;
use App\Filament\Resources\PetActions\Pages\EditPetAction;
use App\Filament\Resources\PetActions\Pages\ListPetActions;
use App\Filament\Resources\PetActions\Schemas\PetActionForm;
use App\Filament\Resources\PetActions\Tables\PetActionsTable;
use App\Models\PetAction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PetActionResource extends Resource
{
    protected static ?string $model = PetAction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'игровое действие';

    protected static ?string $pluralModelLabel = 'игровые действия';

    protected static ?string $navigationLabel = 'Игровые действия';

    public static function form(Schema $schema): Schema
    {
        return PetActionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PetActionsTable::configure($table);
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
            'index' => ListPetActions::route('/'),
            'create' => CreatePetAction::route('/create'),
            'edit' => EditPetAction::route('/{record}/edit'),
        ];
    }
}
