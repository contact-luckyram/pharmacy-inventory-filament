<?php

namespace App\Filament\Resources\Medicines;

use App\Filament\Resources\Medicines\Pages\CreateMedicine;
use App\Filament\Resources\Medicines\Pages\EditMedicine;
use App\Filament\Resources\Medicines\Pages\ListMedicines;
use App\Filament\Resources\Medicines\RelationManagers\BatchesRelationManager;
use App\Filament\Resources\Medicines\Schemas\MedicineForm;
use App\Filament\Resources\Medicines\Tables\MedicinesTable;
use App\Models\Medicine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string | UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MedicineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicinesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSellableStock();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'generic_name', 'sku'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'SKU' => $record->sku,
            'In stock' => (int) $record->stock,
        ];
    }

    /** Red badge in the sidebar with the number of medicines to reorder. */
    public static function getNavigationBadge(): ?string
    {
        $count = Medicine::query()->where('is_active', true)->lowStock()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Medicines at or below reorder level';
    }

    public static function getRelations(): array
    {
        return [
            BatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicines::route('/'),
            'create' => CreateMedicine::route('/create'),
            'edit' => EditMedicine::route('/{record}/edit'),
        ];
    }
}
