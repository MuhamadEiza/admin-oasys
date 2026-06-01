<?php

namespace App\Filament\Resources\SchoolResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SchoolAttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kehadiran Siswa';
    protected static ?string $maxHeight = '300px';
    
    public ?Model $record = null;

    protected function getData(): array
    {
        if (!$this->record) {
            return [
                'datasets' => [],
                'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
            ];
        }

        $schoolId = $this->record->id;
        
        // Cari bulan terakhir yang memiliki data attendances
        $lastMonth = DB::table('attendances')
            ->select(DB::raw('MAX(MONTH(date)) as month, MAX(YEAR(date)) as year'))
            ->where('school_id', $schoolId)
            ->first();
            
        $month = $lastMonth->month ?? now()->month;
        $year = $lastMonth->year ?? now()->year;
        
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $daysEnglish = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        $hadirData = [];
        $sakitIzinData = [];
        $alphaData = [];
        
        for ($i = 0; $i < 5; $i++) {
            $hadir = DB::table('attendances')
                ->where('school_id', $schoolId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereRaw('DAYNAME(date) = ?', [$daysEnglish[$i]])
                ->where('status', 'hadir')
                ->count();
                
            $sakitIzin = DB::table('attendances')
                ->where('school_id', $schoolId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereRaw('DAYNAME(date) = ?', [$daysEnglish[$i]])
                ->whereIn('status', ['sakit', 'izin'])
                ->count();
                
            $alpha = DB::table('attendances')
                ->where('school_id', $schoolId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereRaw('DAYNAME(date) = ?', [$daysEnglish[$i]])
                ->where('status', 'alpha')
                ->count();
            
            $hadirData[] = $hadir;
            $sakitIzinData[] = $sakitIzin;
            $alphaData[] = $alpha;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadirData,
                    'backgroundColor' => '#8CC63F',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Sakit/Izin',
                    'data' => $sakitIzinData,
                    'backgroundColor' => '#FBBF24',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Alpha',
                    'data' => $alphaData,
                    'backgroundColor' => '#EF4444',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}