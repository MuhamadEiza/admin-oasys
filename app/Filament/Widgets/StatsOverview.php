<?php

namespace App\Filament\Widgets;

use App\Models\School;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sekolah', School::count())
                ->description('Seluruh sekolah terdaftar')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('primary'),
            Stat::make('Sekolah Aktif', School::where('status', 'active')->count())
                ->description('Meningkat bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Menunggu Verifikasi', School::where('status', 'pending')->count())
                ->description('Butuh tindakan segera')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}