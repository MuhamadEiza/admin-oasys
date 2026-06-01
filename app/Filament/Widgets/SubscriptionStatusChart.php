<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SubscriptionStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Langganan';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [45, 15, 10, 5],
                    'backgroundColor' => ['#10B981', '#F59E0B', '#EF4444', '#6B7280'],
                ],
            ],
            'labels' => ['Aktif', 'Trial', 'Kadaluarsa', 'Dibatalkan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}