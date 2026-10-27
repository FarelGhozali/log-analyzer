<?php

namespace App\Filament\Resources\ExpertRules\Schemas;

use App\Enums\ThreatLevel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpertRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rule_name')
                    ->label('Nama Aturan')
                    ->placeholder('Contoh: Deteksi Brute Force SSH')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('target_port')
                    ->label('Target Port')
                    ->helperText('Port tujuan yang dipantau, misalnya 22 untuk SSH.')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(65535),
                TextInput::make('min_failed_attempts')
                    ->label('Minimal Percobaan Gagal')
                    ->helperText('Jumlah kegagalan minimum sebelum aturan dianggap terpenuhi.')
                    ->required()
                    ->integer()
                    ->minValue(1),
                Select::make('threat_level')
                    ->label('Tingkat Ancaman')
                    ->options(ThreatLevel::class)
                    ->required()
                    ->native(false),
                Textarea::make('conclusion_message')
                    ->label('Pesan Kesimpulan')
                    ->placeholder('Contoh: Serangan Brute Force SSH terdeteksi!')
                    ->required()
                    ->maxLength(255)
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
