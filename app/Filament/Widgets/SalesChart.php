<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales, last 14 days';

    protected ?string $description = 'Daily revenue in ₹';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $daysAgo) => today()->subDays($daysAgo));

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $days
                        ->map(fn ($day): float => round((float) Sale::whereDate('sold_at', $day)->sum('total'), 2))
                        ->all(),
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($day): string => $day->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
