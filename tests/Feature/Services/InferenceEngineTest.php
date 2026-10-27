<?php

use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use App\Services\InferenceEngine;

it('marks a log as an attack when a rule matches its port and failure threshold', function () {
    $rule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 5]],
    );

    expect($results)->toHaveCount(1)
        ->and($results[0]['status'])->toBe(ScanStatus::Attack)
        ->and($results[0]['triggered_rule_id'])->toBe($rule->id);
});

it('marks a log as safe when no rule matches', function (array $log) {
    ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);

    $results = (new InferenceEngine)->analyze(ExpertRule::query()->get(), [$log]);

    expect($results[0]['status'])->toBe(ScanStatus::Safe)
        ->and($results[0]['triggered_rule_id'])->toBeNull();
})->with([
    'failure count below threshold' => [
        ['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 4],
    ],
    'different target port' => [
        ['source_ip' => '203.0.113.10', 'target_port' => 80, 'failed_attempts' => 99],
    ],
]);

it('triggers the strictest matching rule first', function () {
    $lenientRule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);
    $strictRule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 10,
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 12]],
    );

    expect($results[0]['triggered_rule_id'])->toBe($strictRule->id)
        ->and($results[0]['triggered_rule_id'])->not->toBe($lenientRule->id);
});
