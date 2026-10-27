<?php

namespace App\Models;

use App\Enums\ThreatLevel;
use Database\Factories\ExpertRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'rule_name',
    'target_port',
    'min_failed_attempts',
    'threat_level',
    'conclusion_message',
])]
class ExpertRule extends Model
{
    /** @use HasFactory<ExpertRuleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_port' => 'integer',
            'min_failed_attempts' => 'integer',
            'threat_level' => ThreatLevel::class,
        ];
    }

    /**
     * @return HasMany<ScanResult, $this>
     */
    public function scanResults(): HasMany
    {
        return $this->hasMany(ScanResult::class, 'triggered_rule_id');
    }
}
