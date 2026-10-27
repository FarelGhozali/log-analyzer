<?php

namespace App\Filament\Resources\ExpertRules\Pages;

use App\Filament\Resources\ExpertRules\ExpertRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExpertRules extends ListRecords
{
    protected static string $resource = ExpertRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
