<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Items')
                    ->columnSpan(2)
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('medicine.name')->label('Medicine')->weight('medium'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('unit_price')->label('Price')->money('INR'),
                                TextEntry::make('subtotal')->label('Amount')->money('INR'),
                            ]),
                    ]),
                Section::make('Invoice')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('invoice_number')->copyable()->weight('bold'),
                        TextEntry::make('sold_at')->label('Date')->dateTime('d M Y, h:i A'),
                        TextEntry::make('customer_name')->placeholder('Walk-in customer'),
                        TextEntry::make('customer_phone')->placeholder('—'),
                        TextEntry::make('payment_method')->badge(),
                        TextEntry::make('user.name')->label('Billed by')->placeholder('—'),
                        TextEntry::make('total')->money('INR')->size('lg')->weight('bold'),
                    ]),
            ]);
    }
}
