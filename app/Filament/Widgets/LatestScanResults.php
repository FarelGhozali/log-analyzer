<?php

namespace App\Filament\Widgets;

use App\Enums\ScanStatus;
use App\Models\ScanResult;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class LatestScanResults extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    #[On('scan-completed')]
    public function refreshResults(): void {}

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ScanResult::query()
                ->with('triggeredRule')
                ->latest('created_at')
                ->latest('id')
                ->limit(10))
            ->heading('Hasil Pemindaian Terbaru')
            ->columns([
                TextColumn::make('source_ip')
                    ->label('IP Sumber')
                    ->copyable(),
                TextColumn::make('target_port')
                    ->label('Target Port')
                    ->numeric(),
                TextColumn::make('failed_attempts')
                    ->label('Percobaan Gagal')
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('triggeredRule.rule_name')
                    ->label('Aturan Terpicu')
                    ->placeholder('Tidak ada')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i'),
            ])
            ->recordClasses(fn (ScanResult $record): ?string => $record->status === ScanStatus::Attack
                ? 'bg-danger-50 dark:bg-danger-950/30'
                : null)
            ->paginated(false)
            ->emptyStateHeading('Belum ada hasil pemindaian')
            ->emptyStateDescription('Unggah file log di halaman Log Analyzer untuk memulai analisis.');
    }
}
