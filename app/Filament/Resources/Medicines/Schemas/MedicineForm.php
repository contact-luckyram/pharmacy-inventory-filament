<?php

namespace App\Filament\Resources\Medicines\Schemas;

use App\Enums\MedicineForm as DosageForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MedicineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Medicine details')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Brand name')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('generic_name')
                            ->label('Generic / salt')
                            ->maxLength(150),
                        Select::make('form')
                            ->label('Dosage form')
                            ->options(DosageForm::class)
                            ->required()
                            ->native(false),
                        TextInput::make('strength')
                            ->placeholder('e.g. 500 mg')
                            ->maxLength(50),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')->required()->unique('categories', 'slug'),
                            ]),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Pricing & stock rules')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),
                        TextInput::make('selling_price')
                            ->label('MRP')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->required(),
                        TextInput::make('reorder_level')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(20)
                            ->helperText('Shows a low-stock alert at or below this many units.')
                            ->required(),
                        Toggle::make('requires_prescription')
                            ->label('Prescription (Rx) only'),
                        Toggle::make('is_active')
                            ->label('Available for sale')
                            ->default(true),
                    ]),
            ]);
    }
}
