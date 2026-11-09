<?php

use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use App\Services\InferenceEngine;
use App\Services\LogParser;

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

it('detects a privileged root FTP session from xferlog facts', function () {
    $rule = ExpertRule::factory()->forPort(21)->create([
        'advanced_conditions' => [
            'all' => [
                ['field' => 'service_name', 'operator' => 'equals', 'value' => 'ftp'],
                ['field' => 'username', 'operator' => 'equals', 'value' => 'root'],
            ],
        ],
    ]);
    $logs = (new LogParser)->parse(fixturePath('logs/ftp-xferlog.log'));

    $results = (new InferenceEngine)->analyze(ExpertRule::query()->get(), [$logs[0]]);

    expect($results[0]['triggered_rule_id'])->toBe($rule->id)
        ->and($results[0]['fact_snapshot']['filename'])->toBe('/root/anaconda-ks.cfg')
        ->and($results[0]['raw_log_snippets'])->toHaveCount(1);
});

it('detects a high-frequency completed FTP burst within five seconds', function () {
    $rule = ExpertRule::factory()->forPort(21)->create([
        'advanced_conditions' => [
            'match_type' => 'ftp_burst',
            'threshold' => 10,
            'window_seconds' => 5,
        ],
    ]);
    $logs = array_map(
        fn (int $second): array => [
            'source_ip' => '192.168.203.1',
            'target_port' => 21,
            'failed_attempts' => 0,
            'remote_host' => '192.168.203.1',
            'service_name' => 'ftp',
            'completion_status' => 'c',
            'timestamp' => sprintf('2021-02-28T14:21:%02d+00:00', 50 + intdiv($second, 2)),
            'raw_log' => 'xferlog burst '.$second,
        ],
        range(0, 9),
    );

    $results = (new InferenceEngine)->analyze(ExpertRule::query()->get(), $logs);

    expect($results[9]['triggered_rule_id'])->toBe($rule->id)
        ->and($results[9]['status'])->toBe(ScanStatus::Attack);
});

it('detects sensitive FTP exfiltration and malicious uploads', function (array $ruleConditions, array $log) {
    $rule = ExpertRule::factory()->forPort(21)->create([
        'advanced_conditions' => $ruleConditions,
    ]);

    $results = (new InferenceEngine)->analyze(ExpertRule::query()->get(), [$log]);

    expect($results[0]['triggered_rule_id'])->toBe($rule->id)
        ->and($results[0]['fact_snapshot']['filename'])->toBe($log['filename']);
})->with([
    'sensitive exfiltration' => [
        [
            'all' => [
                ['field' => 'direction', 'operator' => 'equals', 'value' => 'o'],
                ['field' => 'filename', 'operator' => 'regex', 'value' => '/(?:^|\/)(?:etc\/(?:passwd|shadow)|root\/anaconda-ks\.cfg|\.ssh\/.*|\.env(?:$|\/.*)|[^\/]+\.(?:db|sql))$/i'],
            ],
        ],
        [
            'source_ip' => '192.168.203.1',
            'target_port' => 21,
            'failed_attempts' => 0,
            'direction' => 'o',
            'filename' => '/home/admin/.ssh/id_rsa',
        ],
    ],
    'malicious upload' => [
        [
            'all' => [
                ['field' => 'direction', 'operator' => 'equals', 'value' => 'i'],
                ['field' => 'filename', 'operator' => 'regex', 'value' => '/(?:^\/(?:root|usr|etc|var\/www)\/|\\.(?:exe|dll|vbs|sh|php|py|so)$)/i'],
            ],
        ],
        [
            'source_ip' => '192.168.203.1',
            'target_port' => 21,
            'failed_attempts' => 0,
            'direction' => 'i',
            'filename' => '/var/www/html/shell.php',
        ],
    ],
]);
