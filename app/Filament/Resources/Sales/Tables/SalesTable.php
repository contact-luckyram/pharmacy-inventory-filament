<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Enums\PaymentMethod;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('sold_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->searchable()
                    ->placeholder('Walk-in'),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                TextColumn::make('payment_method')
                    ->label('Paid by')
                    ->badge(),
                TextColumn::make('total')
                    ->money('INR')
                    ->sortable()
                    ->summarize(Sum::make()->money('INR')->label('Total')),
            ])
            ->defaultSort('sold_at', 'desc')
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Payment method')
                    ->options(PaymentMethod::class),
                Filter::make('sold_between')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('sold_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('sold_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
