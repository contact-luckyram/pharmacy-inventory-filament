<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Medicines\MedicineResource;
use App\Models\Batch;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringBatches extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Expiring in the next '.Batch::EXPIRY_WARNING_DAYS.' days')
            ->description('Sell these first or return them to the supplier.')
            ->query(Batch::query()->expiringSoon()->with('medicine'))
            ->defaultSort('expires_at')
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('medicine.name')->label('Medicine')->weight('medium'),
                TextColumn::make('batch_number')->label('Batch'),
                TextColumn::make('quantity')->label('Units')->numeric(),
                TextColumn::make('expires_at')
                    ->label('Expiry')
                    ->date('d M Y')
                    ->badge()
                    ->color('warning')
                    ->description(fn (Batch $record): string => $record->expires_at->diffForHumans()),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open medicine')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Batch $record): string => MedicineResource::getUrl('edit', ['record' => $record->medicine_id])),
            ])
            ->emptyStateHeading('No batches expiring soon');
    }
}
