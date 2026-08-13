<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('agent_id')
                    ->numeric(),
                TextInput::make('owner_id')
                    ->numeric(),
                TextInput::make('parcel_number'),
                TextInput::make('title'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('address_line'),
                TextInput::make('city'),
                TextInput::make('region'),
                TextInput::make('country')
                    ->required()
                    ->default('Tanzania'),
                TextInput::make('postal_code'),
                Textarea::make('legal_description')
                    ->columnSpanFull(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Select::make('property_type')
                    ->options([
            'residential' => 'Residential',
            'commercial' => 'Commercial',
            'industrial' => 'Industrial',
            'land' => 'Land',
            'mixed_use' => 'Mixed use',
        ])
                    ->default('residential')
                    ->required(),
                Select::make('listing_type')
                    ->options(['sale' => 'Sale', 'rent' => 'Rent', 'both' => 'Both'])
                    ->default('sale')
                    ->required(),
                TextInput::make('lot_size')
                    ->numeric(),
                TextInput::make('building_area')
                    ->numeric(),
                TextInput::make('bedrooms')
                    ->numeric(),
                TextInput::make('bathrooms')
                    ->numeric(),
                TextInput::make('stories')
                    ->numeric(),
                TextInput::make('year_built')
                    ->numeric(),
                TextInput::make('construction_type'),
                TextInput::make('roof_type'),
                TextInput::make('roof_age_years')
                    ->numeric(),
                TextInput::make('parking_spaces')
                    ->numeric(),
                TextInput::make('parking_type'),
                TextInput::make('zoning_classification'),
                TextInput::make('amenities'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('TZS'),
                TextInput::make('market_value')
                    ->numeric(),
                TextInput::make('tax_value')
                    ->numeric(),
                TextInput::make('hoa_fees')
                    ->numeric(),
                TextInput::make('rental_income')
                    ->numeric(),
                Toggle::make('is_negotiable')
                    ->required(),
                Select::make('status')
                    ->options([
            'active' => 'Active',
            'pending' => 'Pending',
            'sold' => 'Sold',
            'off_market' => 'Off market',
            'rented' => 'Rented',
        ])
                    ->default('active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
                Toggle::make('is_verified')
                    ->required(),
                DateTimePicker::make('listed_at'),
                DateTimePicker::make('sold_at'),
                TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('favorites_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('enquiries_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
