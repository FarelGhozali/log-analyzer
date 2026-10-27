<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ScanStatus: string implements HasColor, HasLabel
{
    case Safe = 'SAFE';
    case Attack = 'ATTACK';

    public function getLabel(): string
    {
        return match ($this) {
            self::Safe => 'Aman',
            self::Attack => 'Serangan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Safe => 'success',
            self::Attack => 'danger',
        };
    }
}
