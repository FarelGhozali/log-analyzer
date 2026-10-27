<?php

namespace App\Services;

use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use Illuminate\Support\Collection;

class InferenceEngine
{
    /**
     * Run forward chaining over log facts using the expert rules as the knowledge base.
     *
     * Rules are evaluated from the strictest minimum failure count to the most lenient,
     * so a stricter rule wins when more than one rule matches the same log entry.
     *
     * @param  Collection<int, ExpertRule>  $rules
     * @param  list<array{source_ip: string, target_port: int, failed_attempts: int}>  $logs
     * @return list<array{source_ip: string, target_port: int, failed_attempts: int, status: ScanStatus, triggered_rule_id: int|null}>
     */
    public function analyze(Collection $rules, array $logs): array
    {
        $orderedRules = $rules
            ->sortBy([['min_failed_attempts', 'desc'], ['id', 'asc']])
            ->values();

        return array_map(function (array $log) use ($orderedRules): array {
            $triggeredRule = $orderedRules->first(
                fn (ExpertRule $rule): bool => $rule->target_port === $log['target_port']
                    && $log['failed_attempts'] >= $rule->min_failed_attempts,
            );

            return [
                ...$log,
                'status' => $triggeredRule !== null ? ScanStatus::Attack : ScanStatus::Safe,
                'triggered_rule_id' => $triggeredRule?->id,
            ];
        }, $logs);
    }
}
