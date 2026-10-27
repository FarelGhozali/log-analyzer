<?php

namespace App\Models;

use App\Enums\ScanStatus;
use Database\Factories\ScanResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'source_ip',
    'target_port',
    'failed_attempts',
    'status',
    'triggered_rule_id',
])]
class ScanResult extends Model
{
    /** @use HasFactory<ScanResultFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_port' => 'integer',
            'failed_attempts' => 'integer',
            'status' => ScanStatus::class,
        ];
    }

    /**
     * @return BelongsTo<ExpertRule, $this>
     */
    public function triggeredRule(): BelongsTo
    {
        return $this->belongsTo(ExpertRule::class, 'triggered_rule_id');
    }

    #[Scope]
    protected function attacks(Builder $query): Builder
    {
        return $query->where('status', ScanStatus::Attack);
    }
}
