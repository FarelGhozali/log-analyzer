<?php

namespace App\Filament\Resources\ExpertRules\Schemas;

use App\Enums\ThreatLevel;
use App\Services\ExpertRuleConditionTemplates;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExpertRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pengaturan Aturan')
                    ->tabs([
                        Tab::make('Pengaturan Dasar')
                            ->schema([
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
                            ]),
                        Tab::make('Pengaturan Lanjutan')
                            ->schema([
                                Select::make('advanced_template')
                                    ->label('Template Kondisi')
                                    ->placeholder('Pilih template untuk mengisi JSON')
                                    ->options(ExpertRuleConditionTemplates::options())
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if ($state !== null) {
                                            $set('advanced_conditions', ExpertRuleConditionTemplates::json($state));
                                        }
                                    })
                                    ->columnSpanFull(),
                                CodeEditor::make('advanced_conditions')
                                    ->label('Kondisi Lanjutan')
                                    ->helperText('Masukkan kondisi aturan dalam format JSON.')
                                    ->language(Language::Json)
                                    ->json()
                                    ->nullable()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
