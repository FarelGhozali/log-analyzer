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

it('evaluates advanced regex conditions after basic conditions', function () {
    $rule = ExpertRule::factory()->forPort(443)->create([
        'min_failed_attempts' => 1,
        'advanced_conditions' => [
            'field' => 'message',
            'operator' => 'regex',
            'value' => '/union\s+select/i',
        ],
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [[
            'source_ip' => '203.0.113.10',
            'target_port' => 443,
            'failed_attempts' => 2,
            'message' => 'UNION SELECT username FROM users',
        ]],
    );

    expect($results[0]['triggered_rule_id'])->toBe($rule->id);
});

it('rejects a log that does not satisfy its advanced conditions', function () {
    ExpertRule::factory()->forPort(443)->create([
        'min_failed_attempts' => 1,
        'advanced_conditions' => [
            'field' => 'uri',
            'operator' => 'regex',
            'value' => '/(?:\.env|\.git)/i',
        ],
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [[
            'source_ip' => '203.0.113.10',
            'target_port' => 443,
            'failed_attempts' => 2,
            'uri' => '/index.php',
        ]],
    );

    expect($results[0]['triggered_rule_id'])->toBeNull()
        ->and($results[0]['status'])->toBe(ScanStatus::Safe);
});

it('evaluates a regex rule when its threshold is met inside the time window', function () {
    $rule = ExpertRule::factory()->forPort(80)->create([
        'advanced_conditions' => [
            'match_type' => 'regex',
            'pattern' => '/failed password/i',
            'time_window_minutes' => 5,
            'threshold' => 2,
        ],
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [
            [
                'source_ip' => '203.0.113.10',
                'target_port' => 80,
                'failed_attempts' => 1,
                'message' => 'Failed password for user',
                'timestamp' => '2026-11-10T10:00:00Z',
            ],
            [
                'source_ip' => '203.0.113.10',
                'target_port' => 443,
                'failed_attempts' => 1,
                'message' => 'FAILED PASSWORD for admin',
                'timestamp' => '2026-11-10T10:04:00Z',
            ],
        ],
    );

    expect($results[1]['triggered_rule_id'])->toBe($rule->id)
        ->and($results[1]['status'])->toBe(ScanStatus::Attack);
});

it('does not trigger a regex rule when matching events are outside the time window', function () {
    ExpertRule::factory()->forPort(80)->create([
        'advanced_conditions' => [
            'match_type' => 'regex',
            'pattern' => '/failed password/i',
            'time_window_minutes' => 5,
            'threshold' => 2,
        ],
    ]);

    $results = (new InferenceEngine)->analyze(
        ExpertRule::query()->get(),
        [
            [
                'source_ip' => '203.0.113.10',
                'target_port' => 80,
                'failed_attempts' => 1,
                'message' => 'Failed password for user',
                'timestamp' => '2026-11-10T10:00:00Z',
            ],
            [
                'source_ip' => '203.0.113.10',
                'target_port' => 80,
                'failed_attempts' => 1,
                'message' => 'FAILED PASSWORD for admin',
                'timestamp' => '2026-11-10T10:06:00Z',
            ],
        ],
    );

    expect($results[1]['triggered_rule_id'])->toBeNull()
        ->and($results[1]['status'])->toBe(ScanStatus::Safe);
});
