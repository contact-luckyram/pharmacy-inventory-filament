<?php

namespace App\Filament\Resources\Medicines\Tables;

use App\Enums\MedicineForm;
use App\Models\Medicine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Medicine')
                    ->description(fn (Medicine $record): ?string => $record->generic_name)
                    ->searchable(['name', 'generic_name'])
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('form')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('strength')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->label('MRP')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('In stock')
                    ->numeric()
                    ->default(0)
                    ->sortable()
                    ->badge()
                    ->color(fn (Medicine $record): string => match (true) {
                        (int) $record->stock === 0 => 'danger',
                        (int) $record->stock <= $record->reorder_level => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('requires_prescription')
                    ->label('Rx')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload(),
                SelectFilter::make('form')
                    ->label('Dosage form')
                    ->options(MedicineForm::class),
                Filter::make('low_stock')
                    ->label('Low stock only')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->lowStock()),
                TernaryFilter::make('requires_prescription')
                    ->label('Prescription only'),
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
