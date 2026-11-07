<?php

namespace App\Filament\Resources\ScanBatches\RelationManagers;

use App\Enums\ScanStatus;
use App\Filament\Resources\ExpertRules\ExpertRuleResource;
use App\Models\ScanResult;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    protected static ?string $title = 'Detail Hasil Pemeriksaan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_ip')
                    ->label('IP Sumber')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('target_port')->label('Target Port')->sortable(),
                TextColumn::make('failed_attempts')->label('Percobaan Gagal')->numeric()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
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
            ])
            ->recordClasses(fn (ScanResult $record): ?string => $record->status === ScanStatus::Attack
                ? 'bg-danger-50 dark:bg-danger-950/30'
                : null)
            ->defaultSort('failed_attempts', 'desc');
    }
}
