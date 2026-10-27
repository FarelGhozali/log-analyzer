<?php

namespace App\Filament\Resources\ExpertRules\Pages;

use App\Filament\Resources\ExpertRules\ExpertRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExpertRule extends EditRecord
{
    protected static string $resource = ExpertRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
