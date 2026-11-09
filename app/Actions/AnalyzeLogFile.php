<?php

namespace App\Actions;

use App\Data\ScanSummary;
use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use App\Models\ScanBatch;
use App\Models\ScanResult;
use App\Services\InferenceEngine;
use App\Services\LogParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyzeLogFile
{
    public function __construct(
        private LogParser $parser,
        private InferenceEngine $engine,
    ) {}

    public function handle(string $path, ?string $fileName = null): ScanSummary
    {
        $logs = $this->parser->parse($path);
        $analysis = $this->engine->analyze(ExpertRule::query()->get(), $logs);
        $attackCount = collect($analysis)
            ->where('status', ScanStatus::Attack)
            ->count();
        $scanBatch = ScanBatch::query()->create([
            'log_file_name' => $fileName ?? 'File tidak diketahui',
            'total_results' => count($analysis),
            'attack_count' => $attackCount,
            'safe_count' => count($analysis) - $attackCount,
        ]);

        DB::transaction(function () use ($analysis, $fileName, $scanBatch): void {
            $timestamp = now();

            collect($analysis)
                ->chunk(500)
                ->each(function (Collection $chunk) use ($timestamp, $fileName, $scanBatch): void {
                    ScanResult::query()->insert(
                        $chunk->map(fn (array $result): array => [
                            'log_file_name' => $fileName,
                            'scan_batch_id' => $scanBatch->id,
                            'source_ip' => $result['source_ip'],
                            'target_port' => $result['target_port'],
                            'failed_attempts' => $result['failed_attempts'],
                            'raw_log_snippets' => json_encode($result['raw_log_snippets'] ?? [$result['raw_log'] ?? '']),
                            'event_timestamp' => $result['timestamp'] ?? null,
                            'fact_snapshot' => json_encode($result['fact_snapshot'] ?? []),
                            'explanation' => json_encode($result['explanation'] ?? []),
                            'status' => $result['status']->value,
                            'triggered_rule_id' => $result['triggered_rule_id'],
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ])->all(),
                    );
                });
        });

        return new ScanSummary(
            total: count($analysis),
            attacks: $attackCount,
            safe: count($analysis) - $attackCount,
        );
    }
}
