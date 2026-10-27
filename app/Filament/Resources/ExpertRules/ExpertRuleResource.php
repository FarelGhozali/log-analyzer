<?php

namespace App\Filament\Resources\ExpertRules;

use App\Filament\Resources\ExpertRules\Pages\CreateExpertRule;
use App\Filament\Resources\ExpertRules\Pages\EditExpertRule;
use App\Filament\Resources\ExpertRules\Pages\ListExpertRules;
use App\Filament\Resources\ExpertRules\Pages\ViewExpertRule;
use App\Filament\Resources\ExpertRules\Schemas\ExpertRuleForm;
use App\Filament\Resources\ExpertRules\Schemas\ExpertRuleInfolist;
use App\Filament\Resources\ExpertRules\Tables\ExpertRulesTable;
use App\Models\ExpertRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExpertRuleResource extends Resource
{
    protected static ?string $model = ExpertRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Sistem Pakar';

    protected static ?string $navigationLabel = 'Aturan Pakar';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Aturan Pakar';

    protected static ?string $pluralModelLabel = 'Aturan Pakar';

    protected static ?string $recordTitleAttribute = 'rule_name';

    public static function form(Schema $schema): Schema
    {
        return ExpertRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExpertRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpertRulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpertRules::route('/'),
            'create' => CreateExpertRule::route('/create'),
            'view' => ViewExpertRule::route('/{record}'),
            'edit' => EditExpertRule::route('/{record}/edit'),
        ];
    }
}
