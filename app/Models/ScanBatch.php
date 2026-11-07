<?php

namespace App\Models;

use Database\Factories\ScanBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['log_file_name', 'total_results', 'attack_count', 'safe_count'])]
class ScanBatch extends Model
{
    /** @use HasFactory<ScanBatchFactory> */
    use HasFactory;

    /**
     * @return HasMany<ScanResult, $this>
     */
    public function results(): HasMany
    {
        return $this->hasMany(ScanResult::class);
    }
}
