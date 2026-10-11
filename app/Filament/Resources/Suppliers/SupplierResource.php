<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\ManageSuppliers;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string | UnitEnum | null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Company name')->required()->maxLength(150),
                TextInput::make('contact_person')->maxLength(100),
                TextInput::make('phone')->tel()->required()->maxLength(20),
                TextInput::make('email')->email()->maxLength(150),
                TextInput::make('gstin')
                    ->label('GSTIN')
                    ->length(15)
                    ->regex('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/')
                    ->validationMessages(['regex' => 'Enter a valid 15-character GSTIN.'])
                    ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : null),
                Textarea::make('address')->rows(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('contact_person')->searchable()->placeholder('—'),
                TextColumn::make('phone')->icon(Heroicon::OutlinedPhone)->copyable(),
                TextColumn::make('email')->placeholder('—')->toggleable(),
                TextColumn::make('gstin')->label('GSTIN')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('medicines_count')->label('Medicines')->counts('medicines')->badge()->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSuppliers::route('/'),
        ];
    }
}
