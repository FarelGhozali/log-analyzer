<?php

namespace App\Filament\Resources\ScanBatches;

use App\Filament\Resources\ScanBatches\Pages\ListScanBatches;
use App\Filament\Resources\ScanBatches\Pages\ViewScanBatch;
use App\Filament\Resources\ScanBatches\RelationManagers\ResultsRelationManager;
use App\Filament\Resources\ScanBatches\Tables\ScanBatchesTable;
use App\Models\ScanBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScanBatchResource extends Resource
{
    protected static ?string $model = ScanBatch::class;

    protected static ?string $slug = 'scan-results';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem Pakar';

    protected static ?string $navigationLabel = 'Hasil Analisis';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Sesi Analisis';

    protected static ?string $pluralModelLabel = 'Sesi Analisis';

    public static function table(Table $table): Table
    {
        return ScanBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScanBatches::route('/'),
            'view' => ViewScanBatch::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ScanBatch::query()->where('attack_count', '>', 0)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
