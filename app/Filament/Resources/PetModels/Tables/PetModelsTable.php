<?php

namespace App\Filament\Resources\PetModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PetModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Модель')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label('Тип')
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable(),
                TextColumn::make('asset_path')
                    ->label('GLB-путь')
                    ->limit(45),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
