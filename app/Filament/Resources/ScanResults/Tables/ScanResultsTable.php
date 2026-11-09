<?php

namespace App\Filament\Resources\ScanResults\Tables;

use App\Enums\ScanStatus;
use App\Filament\Resources\ExpertRules\ExpertRuleResource;
use App\Models\ScanResult;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScanResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_file_name')
                    ->label('File Log')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak diketahui')
                    ->copyable(),
                TextColumn::make('source_ip')
                    ->label('IP Sumber')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('target_port')
                    ->label('Target Port')
                    ->sortable(),
                TextColumn::make('failed_attempts')
                    ->label('Percobaan Gagal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('triggeredRule.rule_name')
                    ->label('Aturan Terpicu')
                    ->placeholder('Tidak ada')
                    ->wrap()
                    ->url(fn (ScanResult $record): ?string => $record->triggeredRule !== null
                        ? ExpertRuleResource::getUrl('view', ['record' => $record->triggeredRule])
                        : null),
                TextColumn::make('triggeredRule.threat_level')
                    ->label('Tingkat Ancaman')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Waktu Analisis')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ScanStatus::class),
                Filter::make('attacks')
                    ->label('Hanya tampilkan serangan')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->attacks()),
            ])
            ->recordClasses(fn (ScanResult $record): ?string => $record->status === ScanStatus::Attack
                ? 'bg-danger-50 dark:bg-danger-950/30'
                : null)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus hasil analisis?')
                    ->modalDescription('Hasil analisis ini akan dihapus permanen dari database.')
                    ->successNotificationTitle('Hasil analisis dihapus permanen'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada hasil analisis')
            ->emptyStateDescription('Unggah file log pada halaman Log Analyzer untuk memulai pemindaian.');
    }
}
