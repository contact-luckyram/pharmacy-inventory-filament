<?php

namespace App\Filament\Widgets;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PharmacyStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todaySales = (float) Sale::whereDate('sold_at', today())->sum('total');
        $yesterdaySales = (float) Sale::whereDate('sold_at', today()->subDay())->sum('total');

        $lastSevenDays = collect(range(6, 0))
            ->map(fn (int $daysAgo): float => (float) Sale::whereDate('sold_at', today()->subDays($daysAgo))->sum('total'))
            ->all();

        $change = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : null;

        $lowStock = Medicine::where('is_active', true)->lowStock()->count();
        $expiring = Batch::expiringSoon()->count();

        $stockValue = (float) Batch::sellable()->selectRaw('coalesce(sum(quantity * cost_price), 0) as value')->value('value');

        return [
            Stat::make("Today's sales", Number::currency($todaySales, 'INR', 'en_IN'))
                ->description($change === null ? 'No sales yesterday' : sprintf('%+.0f%% vs yesterday', $change))
                ->descriptionIcon($change !== null && $change < 0 ? Heroicon::ArrowTrendingDown : Heroicon::ArrowTrendingUp)
                ->color($change !== null && $change < 0 ? 'danger' : 'success')
                ->chart($lastSevenDays),

            Stat::make('Stock value', Number::currency($stockValue, 'INR', 'en_IN'))
                ->description('At cost, unexpired batches')
                ->descriptionIcon(Heroicon::OutlinedArchiveBox)
                ->color('primary'),

            Stat::make('Low stock', $lowStock)
                ->description($lowStock ? 'Medicines to reorder' : 'All stocked up')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($lowStock ? 'warning' : 'success'),

            Stat::make('Expiring soon', $expiring)
                ->description('Batches in the next '.Batch::EXPIRY_WARNING_DAYS.' days')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($expiring ? 'danger' : 'success'),
        ];
    }
}
