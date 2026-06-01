<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SchoolRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendaftaran Sekolah';
    protected static ?int $sort = 2; // Tampil setelah StatsOverview

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Sekolah Mendaftar',
                    'data' => [4, 7, 12, 15, 25, 32, 40],
                    'backgroundColor' => '#8CC63F',
                    'borderColor' => '#8CC63F',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}