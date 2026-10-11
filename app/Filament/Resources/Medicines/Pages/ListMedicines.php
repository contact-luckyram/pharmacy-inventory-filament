<?php

namespace App\Filament\Resources\Medicines\Pages;

use App\Filament\Resources\Medicines\MedicineResource;
use App\Models\Medicine;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'low_stock' => Tab::make('Low stock')
                ->badge(Medicine::query()->lowStock()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->lowStock()),
            'rx' => Tab::make('Prescription only')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('requires_prescription', true)),
        ];
    }
}
