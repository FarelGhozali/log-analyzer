<?php

namespace App\Filament\Resources\ExpertRules\Pages;

use App\Filament\Resources\ExpertRules\ExpertRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExpertRule extends ViewRecord
{
    protected static string $resource = ExpertRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
