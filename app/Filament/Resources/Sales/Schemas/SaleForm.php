<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\PaymentMethod;
use App\Models\Medicine;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Items')
                    ->columnSpan(2)
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship()
                            ->columns(12)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Add medicine')
                            ->live()
                            // Runs when a row is added or removed.
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotal($get, $set, 'items', 'total'))
                            ->schema([
                                Select::make('medicine_id')
                                    ->label('Medicine')
                                    ->options(fn (): array => self::sellableMedicineOptions())
                                    ->searchable()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                        $set('unit_price', Medicine::find($state)?->selling_price);
                                        self::updateTotal($get, $set, '../../items', '../../total');
                                    })
                                    ->columnSpan(6),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn (Get $get): ?int => Medicine::find($get('medicine_id'))?->availableStock())
                                    ->validationMessages(['max' => 'Only :max units in stock.'])
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotal($get, $set, '../../items', '../../total'))
                                    ->columnSpan(3),
                                TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₹')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotal($get, $set, '../../items', '../../total'))
                                    ->columnSpan(3),
                            ]),
                    ]),

                Section::make('Customer & payment')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('customer_name')->maxLength(100),
                        TextInput::make('customer_phone')->tel()->maxLength(20),
                        ToggleButtons::make('payment_method')
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::Cash)
                            ->inline()
                            ->required(),
                        TextInput::make('total')
                            ->label('Bill total')
                            ->prefix('₹')
                            ->readOnly()
                            ->default('0.00')
                            // The saved total is recalculated from the items on the server.
                            ->dehydrated(false)
                            ->extraInputAttributes(['class' => 'text-lg font-semibold']),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function sellableMedicineOptions(): array
    {
        return Medicine::query()
            ->where('is_active', true)
            ->withSellableStock()
            ->orderBy('name')
            ->get()
            ->filter(fn (Medicine $medicine): bool => (int) $medicine->stock > 0)
            ->mapWithKeys(fn (Medicine $medicine): array => [
                $medicine->id => "{$medicine->displayName()} · {$medicine->stock} in stock",
            ])
            ->all();
    }

    public static function updateTotal(Get $get, Set $set, string $itemsPath, string $totalPath): void
    {
        $total = collect($get($itemsPath) ?? [])
            ->sum(fn (array $item): float => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0));

        $set($totalPath, number_format($total, 2, '.', ''));
    }
}
