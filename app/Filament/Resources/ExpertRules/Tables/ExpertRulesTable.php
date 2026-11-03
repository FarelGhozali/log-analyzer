<?php

namespace App\Filament\Resources\ExpertRules\Tables;

use App\Enums\ThreatLevel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpertRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rule_name')
                    ->label('Nama Aturan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('target_port')
                    ->label('Target Port')
                    ->sortable(),
                TextColumn::make('min_failed_attempts')
                    ->label('Min. Percobaan Gagal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('threat_level')
                    ->label('Tingkat Ancaman')
                    ->badge()
                    ->sortable(),
                TextColumn::make('scan_results_count')
                    ->label('Total Deteksi')
                    ->counts('scanResults')
                    ->badge()
                    ->color('danger'),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('threat_level')
                    ->label('Tingkat Ancaman')
                    ->options(ThreatLevel::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada aturan pakar')
            ->emptyStateDescription('Tambahkan aturan IF-THEN agar mesin inferensi dapat mendeteksi serangan.');
    }
}
