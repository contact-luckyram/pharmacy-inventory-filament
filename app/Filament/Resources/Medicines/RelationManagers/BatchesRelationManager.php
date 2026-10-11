<?php

namespace App\Filament\Resources\Medicines\RelationManagers;

use App\Models\Batch;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

/**
 * Stock arrives in batches. Each batch has its own expiry date,
 * so the app can sell the oldest stock first and warn before it expires.
 */
class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Stock batches';

    protected static ?string $recordTitleAttribute = 'batch_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('batch_number')
                    ->required()
                    ->maxLength(50)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('medicine_id', $this->getOwnerRecord()->getKey()),
                    ),
                TextInput::make('quantity')
                    ->label('Units received')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->required(),
                TextInput::make('cost_price')
                    ->label('Cost per unit')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₹')
                    ->required(),
                DatePicker::make('manufactured_at')
                    ->label('Manufactured on')
                    ->maxDate(today())
                    ->native(false),
                DatePicker::make('expires_at')
                    ->label('Expiry date')
                    ->required()
                    ->native(false)
                    ->after(fn (Get $get) => $get('manufactured_at') ?: null),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_number')->searchable()->weight('medium'),
                TextColumn::make('quantity')
                    ->label('Units')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total units')),
                TextColumn::make('cost_price')->label('Cost / unit')->money('INR'),
                TextColumn::make('manufactured_at')->label('Mfg.')->date('M Y')->placeholder('—'),
                TextColumn::make('expires_at')
                    ->label('Expiry')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Batch $record): string => match ($record->expiryStatus()) {
                        'expired' => 'danger',
                        'expiring' => 'warning',
                        default => 'success',
                    })
                    ->description(fn (Batch $record): string => $record->expiryStatus() === 'expired'
                        ? 'Expired'
                        : $record->expires_at->diffForHumans()),
            ])
            ->defaultSort('expires_at')
            ->filters([
                TernaryFilter::make('expired')
                    ->label('Expiry')
                    ->placeholder('All batches')
                    ->trueLabel('Expired only')
                    ->falseLabel('Not expired')
                    ->queries(
                        true: fn (Builder $query) => $query->expired(),
                        false: fn (Builder $query) => $query->sellable(),
                    ),
            ])
            ->headerActions([
                CreateAction::make()->label('Receive stock'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
