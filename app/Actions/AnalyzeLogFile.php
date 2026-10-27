<?php

namespace App\Actions;

use App\Data\ScanSummary;
use App\Enums\ScanStatus;
use App\Models\ExpertRule;
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

    public function handle(string $path): ScanSummary
    {
        $logs = $this->parser->parse($path);
        $analysis = $this->engine->analyze(ExpertRule::query()->get(), $logs);
        $attackCount = collect($analysis)
            ->where('status', ScanStatus::Attack)
            ->count();

        DB::transaction(function () use ($analysis): void {
            $timestamp = now();

            collect($analysis)
                ->chunk(500)
                ->each(function (Collection $chunk) use ($timestamp): void {
                    ScanResult::query()->insert(
                        $chunk->map(fn (array $result): array => [
                            'source_ip' => $result['source_ip'],
                            'target_port' => $result['target_port'],
                            'failed_attempts' => $result['failed_attempts'],
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
