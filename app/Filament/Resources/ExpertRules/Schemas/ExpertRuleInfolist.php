<?php

namespace App\Filament\Resources\ExpertRules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpertRuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('rule_name')
                    ->label('Nama Aturan'),
                TextEntry::make('target_port')
                    ->label('Target Port'),
                TextEntry::make('min_failed_attempts')
                    ->label('Minimal Percobaan Gagal')
                    ->numeric(),
                TextEntry::make('threat_level')
                    ->label('Tingkat Ancaman')
                    ->badge(),
                TextEntry::make('conclusion_message')
                    ->label('Pesan Kesimpulan')
                    ->columnSpanFull(),
                TextEntry::make('mitigation_recommendation')
                    ->label('Rekomendasi Mitigasi')
                    ->placeholder('Belum ditentukan')
                    ->columnSpanFull(),
                TextEntry::make('advanced_conditions')
                    ->label('Kondisi Lanjutan')
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_string($state)) {
                            return $state;
                        }

                        return is_array($state)
                            ? (json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            : 'Mode dasar';
                    })
                    ->prose()
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
