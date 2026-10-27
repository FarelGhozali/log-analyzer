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
                    ->label('Target Port')
                    ->numeric(),
                TextEntry::make('min_failed_attempts')
                    ->label('Minimal Percobaan Gagal')
                    ->numeric(),
                TextEntry::make('threat_level')
                    ->label('Tingkat Ancaman')
                    ->badge(),
                TextEntry::make('conclusion_message')
                    ->label('Pesan Kesimpulan')
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
