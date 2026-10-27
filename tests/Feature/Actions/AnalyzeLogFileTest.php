<?php

use App\Actions\AnalyzeLogFile;
use App\Enums\ScanStatus;
use App\Exceptions\InvalidLogFileException;
use App\Models\ExpertRule;
use App\Models\ScanResult;

it('persists an attack or safe result for every parsed log entry', function () {
    $sshRule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);
    ExpertRule::factory()->forPort(443)->create([
        'min_failed_attempts' => 10,
    ]);
    ExpertRule::factory()->forPort(3306)->create([
        'min_failed_attempts' => 10,
    ]);

    $summary = app(AnalyzeLogFile::class)->handle(fixturePath('logs/traffic.csv'));

    expect($summary->total)->toBe(4)
        ->and($summary->attacks)->toBe(3)
        ->and($summary->safe)->toBe(1)
        ->and(ScanResult::query()->count())->toBe(4)
        ->and(ScanResult::query()->attacks()->count())->toBe(3);

    $this->assertDatabaseHas('scan_results', [
        'source_ip' => '203.0.113.10',
        'target_port' => 22,
        'failed_attempts' => 8,
        'status' => ScanStatus::Attack->value,
        'triggered_rule_id' => $sshRule->id,
    ]);

    $this->assertDatabaseHas('scan_results', [
        'source_ip' => '198.51.100.20',
        'target_port' => 22,
        'failed_attempts' => 2,
        'status' => ScanStatus::Safe->value,
        'triggered_rule_id' => null,
    ]);
});

it('does not persist results when the log file is invalid', function () {
    ExpertRule::factory()->forPort(22)->create();

    expect(fn () => app(AnalyzeLogFile::class)->handle(fixturePath('logs/invalid-ip.csv')))
        ->toThrow(InvalidLogFileException::class);

    expect(ScanResult::query()->count())->toBe(0);
});
