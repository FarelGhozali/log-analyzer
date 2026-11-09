<?php

namespace App\Filament\Resources\ScanResults\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScanResultInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('status')
                    ->label('Status')
                    ->badge(),
                TextEntry::make('log_file_name')
                    ->label('File Log')
                    ->placeholder('Tidak diketahui'),
                TextEntry::make('source_ip')
                    ->label('IP Sumber')
                    ->copyable(),
                TextEntry::make('target_port')
                    ->label('Target Port')
                    ->numeric(),
                TextEntry::make('failed_attempts')
                    ->label('Percobaan Gagal Aktual')
                    ->numeric(),
                TextEntry::make('event_timestamp')
                    ->label('Timestamp Kejadian')
                    ->dateTime('d M Y H:i:s')
                    ->placeholder('Tidak tersedia'),
                TextEntry::make('triggeredRule.rule_name')
                    ->label('Nama Aturan Terpicu')
                    ->placeholder('Tidak ada'),
                TextEntry::make('triggeredRule.target_port')
                    ->label('Target Port Aturan')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('triggeredRule.min_failed_attempts')
                    ->label('Ambang Batas Minimal Aturan')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('explanation.comparison')
                    ->label('Perbandingan Fakta vs Aturan')
                    ->placeholder('Tidak tersedia')
                    ->columnSpanFull(),
                TextEntry::make('fact_snapshot')
                    ->label('Snapshot Fakta')
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_string($state)) {
                            return $state;
                        }

                        return is_array($state)
                            ? (json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            : 'Tidak tersedia';
                    })
                    ->prose()
                    ->columnSpanFull(),
                TextEntry::make('raw_log_snippets')
                    ->label('Bukti Raw Log')
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_string($state)) {
                            return $state;
                        }

                        return is_array($state) ? implode("\n", $state) : 'Tidak tersedia';
                    })
                    ->prose()
                    ->columnSpanFull(),
                TextEntry::make('triggeredRule.mitigation_recommendation')
                    ->label('Rekomendasi Mitigasi')
                    ->placeholder('Belum ditentukan')
                    ->columnSpanFull(),
            ]);
    }
}
