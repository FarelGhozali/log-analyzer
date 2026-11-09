<?php

namespace App\Services;

use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
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
     * @param  list<array<string, mixed>>  $logs
     * @return list<array<string, mixed>>
     */
    public function analyze(Collection $rules, array $logs): array
    {
        $orderedRules = $rules
            ->sortBy([['min_failed_attempts', 'desc'], ['id', 'asc']])
            ->values();
        $portsBySourceIp = collect($logs)
            ->groupBy('source_ip')
            ->map(fn (Collection $sourceLogs): int => $sourceLogs->pluck('target_port')->unique()->count());

        return array_map(function (array $log) use ($orderedRules, $logs, $portsBySourceIp): array {
            $log['distinct_target_ports'] ??= $portsBySourceIp->get($log['source_ip'], 0);

            $triggeredRule = $orderedRules->first(
                fn (ExpertRule $rule): bool => $rule->advanced_conditions !== null
                    ? $this->evaluateJsonRule($rule, $log, $logs)
                    : $rule->target_port === $log['target_port']
                        && $log['failed_attempts'] >= $rule->min_failed_attempts,
            );

            $result = [
                ...$log,
                'status' => $triggeredRule !== null ? ScanStatus::Attack : ScanStatus::Safe,
                'triggered_rule_id' => $triggeredRule?->id,
            ];

            if ($triggeredRule !== null) {
                $result['fact_snapshot'] = [
                    'source_ip' => $log['source_ip'] ?? null,
                    'target_port' => $log['target_port'] ?? null,
                    'failed_attempts' => $log['failed_attempts'] ?? null,
                    'timestamp' => $log['timestamp'] ?? null,
                ];
                $result['raw_log_snippets'] = $this->evidenceForRule($triggeredRule, $log, $logs);
                $result['explanation'] = $this->buildExplanation($triggeredRule, $log, $logs);
            }

            return $result;
        }, $logs);
    }

    /**
     * @param  array<string, mixed>  $log
     * @param  list<array<string, mixed>>  $logs
     * @return list<string>
     */
    private function evidenceForRule(ExpertRule $rule, array $log, array $logs): array
    {
        $evidence = [];
        $conditions = $rule->advanced_conditions;
        $isRegexRule = $conditions !== null
            && ($conditions['match_type'] ?? null) === 'regex';
        $windowStart = null;
        $logTimestamp = $this->parseTimestamp($log['timestamp'] ?? null);

        if ($isRegexRule
            && $logTimestamp !== null
            && isset($conditions['time_window_minutes'])
            && is_numeric($conditions['time_window_minutes'])
            && (int) $conditions['time_window_minutes'] >= 0) {
            $windowStart = $logTimestamp->subMinutes((int) $conditions['time_window_minutes']);
        }

        foreach ($logs as $candidate) {
            if (! $this->belongsToSameSource($candidate, $log)) {
                continue;
            }

            if ($rule->advanced_conditions === null
                && (int) ($candidate['target_port'] ?? 0) !== $rule->target_port) {
                continue;
            }

            if ($isRegexRule
                && ! $this->matchesRegex($candidate, (string) ($conditions['pattern'] ?? ''))) {
                continue;
            }

            if ($windowStart !== null) {
                $candidateTimestamp = $this->parseTimestamp($candidate['timestamp'] ?? null);

                if ($candidateTimestamp === null
                    || $logTimestamp === null
                    || ! $candidateTimestamp->betweenIncluded($windowStart, $logTimestamp)) {
                    continue;
                }
            }

            $snippets = $candidate['raw_log_snippets'] ?? [$candidate['raw_log'] ?? null];

            foreach ($snippets as $snippet) {
                if (is_string($snippet) && $snippet !== '') {
                    $evidence[] = $snippet;
                }
            }
        }

        return array_values(array_unique($evidence));
    }

    /**
     * @param  array<string, mixed>  $log
     * @param  list<array<string, mixed>>  $logs
     * @return array<string, mixed>
     */
    private function buildExplanation(ExpertRule $rule, array $log, array $logs): array
    {
        $isAdvanced = $rule->advanced_conditions !== null;
        $actualAttempts = (int) ($log['failed_attempts'] ?? 0);
        $comparison = $isAdvanced
            ? 'Kondisi lanjutan pada aturan terpenuhi berdasarkan fakta log.'
            : sprintf(
                'Ditemukan %d percobaan gagal dari IP %s pada port %d, melampaui syarat minimal %d percobaan.',
                $actualAttempts,
                $log['source_ip'] ?? '-',
                (int) ($log['target_port'] ?? 0),
                $rule->min_failed_attempts,
            );

        return [
            'rule' => [
                'id' => $rule->id,
                'name' => $rule->rule_name,
                'target_port' => $rule->target_port,
                'minimum_failed_attempts' => $rule->min_failed_attempts,
                'advanced_conditions' => $rule->advanced_conditions,
            ],
            'facts' => [
                'source_ip' => $log['source_ip'] ?? null,
                'target_port' => $log['target_port'] ?? null,
                'actual_failed_attempts' => $log['failed_attempts'] ?? null,
                'timestamp' => $log['timestamp'] ?? null,
            ],
            'comparison' => $comparison,
            'evidence_count' => count($this->evidenceForRule($rule, $log, $logs)),
        ];
    }

    /**
     * Evaluate the JSON rule format without changing the existing basic-rule path.
     *
     * @param  array<string, mixed>  $log
     * @param  list<array<string, mixed>>  $logs
     */
    private function evaluateJsonRule(ExpertRule $rule, array $log, array $logs): bool
    {
        $conditions = $rule->advanced_conditions;

        if (! array_key_exists('match_type', $conditions)) {
            return $this->matchesAdvancedConditions($rule, $log);
        }

        $matchType = $conditions['match_type'];
        $pattern = $conditions['pattern'] ?? null;
        $threshold = $conditions['threshold'] ?? 1;
        $timeWindowMinutes = $conditions['time_window_minutes'] ?? null;

        if ($matchType !== 'regex' || ! is_string($pattern) || ! is_numeric($threshold)) {
            return false;
        }

        $threshold = (int) $threshold;

        if ($threshold < 1) {
            return false;
        }

        $matchingLogs = array_filter(
            $logs,
            fn (array $candidate): bool => $this->matchesRegex($candidate, $pattern)
                && $this->belongsToSameSource($candidate, $log),
        );

        if ($timeWindowMinutes === null) {
            return count($matchingLogs) >= $threshold;
        }

        if (! is_numeric($timeWindowMinutes) || (int) $timeWindowMinutes < 0) {
            return false;
        }

        $logTimestamp = $this->parseTimestamp($log['timestamp'] ?? null);

        if ($logTimestamp === null) {
            return false;
        }

        $windowStart = $logTimestamp->subMinutes((int) $timeWindowMinutes);
        $matchingLogsInWindow = array_filter(
            $matchingLogs,
            function (array $candidate) use ($logTimestamp, $windowStart): bool {
                $candidateTimestamp = $this->parseTimestamp($candidate['timestamp'] ?? null);

                return $candidateTimestamp !== null
                    && $candidateTimestamp->betweenIncluded($windowStart, $logTimestamp);
            },
        );

        return count($matchingLogsInWindow) >= $threshold;
    }

    /**
     * @param  array<string, mixed>  $log
     */
    private function matchesRegex(array $log, string $pattern): bool
    {
        foreach ($log as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            if (preg_match($pattern, (string) $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>  $log
     */
    private function belongsToSameSource(array $candidate, array $log): bool
    {
        return ! array_key_exists('source_ip', $candidate)
            || ! array_key_exists('source_ip', $log)
            || $candidate['source_ip'] === $log['source_ip'];
    }

    private function parseTimestamp(mixed $timestamp): ?CarbonImmutable
    {
        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($timestamp);
        } catch (InvalidFormatException) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $log
     */
    private function matchesAdvancedConditions(ExpertRule $rule, array $log): bool
    {
        $conditions = $rule->advanced_conditions;

        if ($conditions === []) {
            return true;
        }

        if (isset($conditions['all']) && is_array($conditions['all'])) {
            return collect($conditions['all'])
                ->every(fn (mixed $condition): bool => is_array($condition) && $this->matchesCondition($condition, $log));
        }

        if (isset($conditions['any']) && is_array($conditions['any'])) {
            return collect($conditions['any'])
                ->contains(fn (mixed $condition): bool => is_array($condition) && $this->matchesCondition($condition, $log));
        }

        return $this->matchesCondition($conditions, $log);
    }

    /**
     * @param  array<string, mixed>  $condition
     * @param  array<string, mixed>  $log
     */
    private function matchesCondition(array $condition, array $log): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        if (! is_string($field) || ! is_string($operator) || ! array_key_exists($field, $log)) {
            return false;
        }

        $actual = $log[$field];

        return match ($operator) {
            'equals' => $actual === $value || (is_numeric($actual) && is_numeric($value) && (float) $actual === (float) $value),
            'not_equals' => ! $this->matchesCondition([...$condition, 'operator' => 'equals'], $log),
            'contains' => is_string($actual) && is_string($value) && str_contains(strtolower($actual), strtolower($value)),
            'regex' => is_string($actual) && is_string($value) && preg_match($value, $actual) === 1,
            'in' => is_array($value) && in_array($actual, $value, true),
            'greater_than' => is_numeric($actual) && is_numeric($value) && (float) $actual > (float) $value,
            'greater_than_or_equal' => is_numeric($actual) && is_numeric($value) && (float) $actual >= (float) $value,
            'less_than' => is_numeric($actual) && is_numeric($value) && (float) $actual < (float) $value,
            'less_than_or_equal' => is_numeric($actual) && is_numeric($value) && (float) $actual <= (float) $value,
            'between' => is_array($value) && count($value) === 2 && $actual >= $value[0] && $actual <= $value[1],
            default => false,
        };
    }
}
