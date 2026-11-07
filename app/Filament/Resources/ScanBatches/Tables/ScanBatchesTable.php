<?php

namespace App\Filament\Resources\ScanBatches\Tables;

use App\Filament\Resources\ScanBatches\ScanBatchResource;
use App\Models\ScanBatch;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScanBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_file_name')
                    ->label('File Log')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('total_results')
                    ->label('Total Fakta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('attack_count')
                    ->label('Serangan')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                TextColumn::make('safe_count')
                    ->label('Aman')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label('Waktu Analisis')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordUrl(fn (ScanBatch $record): string => ScanBatchResource::getUrl('view', ['record' => $record]))
            ->recordClasses(fn (ScanBatch $record): ?string => $record->attack_count > 0
                ? 'bg-danger-50 dark:bg-danger-950/30'
                : null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada hasil analisis')
            ->emptyStateDescription('Unggah file log pada halaman Log Analyzer untuk memulai pemindaian.');
    }
}
