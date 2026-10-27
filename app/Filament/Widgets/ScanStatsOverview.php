<?php

namespace App\Filament\Widgets;

use App\Models\ExpertRule;
use App\Models\ScanResult;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ScanStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $ruleCount = ExpertRule::query()->count();
        $totalScans = ScanResult::query()->count();
        $attackCount = ScanResult::query()->attacks()->count();
        $safeCount = $totalScans - $attackCount;

        return [
            Stat::make('Aturan Pakar', number_format($ruleCount))
                ->description('Aturan aktif pada knowledge base')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('primary'),
            Stat::make('Baris Log Dianalisis', number_format($totalScans))
                ->description('Total fakta log yang telah diproses')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('info'),
            Stat::make('Serangan Terdeteksi', number_format($attackCount))
                ->description($attackCount > 0 ? 'Perlu tindak lanjut keamanan' : 'Tidak ada ancaman terdeteksi')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($attackCount > 0 ? 'danger' : 'success'),
            Stat::make('Lalu Lintas Aman', number_format($safeCount))
                ->description('Baris log tanpa indikasi serangan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
