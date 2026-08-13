<?php

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agent_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('owner_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parcel_number')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('address_line')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('region')
                    ->searchable(),
                TextColumn::make('country')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('property_type')
                    ->badge(),
                TextColumn::make('listing_type')
                    ->badge(),
                TextColumn::make('lot_size')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('building_area')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bedrooms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bathrooms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stories')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('year_built')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('construction_type')
                    ->searchable(),
                TextColumn::make('roof_type')
                    ->searchable(),
                TextColumn::make('roof_age_years')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parking_spaces')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parking_type')
                    ->searchable(),
                TextColumn::make('zoning_classification')
                    ->searchable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('market_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('hoa_fees')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rental_income')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_negotiable')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge(),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_verified')
                    ->boolean(),
                TextColumn::make('listed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sold_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('views_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('favorites_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enquiries_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
