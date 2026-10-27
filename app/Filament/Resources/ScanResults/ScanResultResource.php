<?php

namespace App\Filament\Resources\ScanResults;

use App\Filament\Resources\ScanResults\Pages\ListScanResults;
use App\Filament\Resources\ScanResults\Tables\ScanResultsTable;
use App\Models\ScanResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScanResultResource extends Resource
{
    protected static ?string $model = ScanResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem Pakar';

    protected static ?string $navigationLabel = 'Hasil Analisis';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Hasil Analisis';

    protected static ?string $pluralModelLabel = 'Hasil Analisis';

    protected static ?string $recordTitleAttribute = 'source_ip';

    public static function table(Table $table): Table
    {
        return ScanResultsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $attackCount = ScanResult::query()->attacks()->count();

        return $attackCount > 0 ? (string) $attackCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScanResults::route('/'),
        ];
    }
}
