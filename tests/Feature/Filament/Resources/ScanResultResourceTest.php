<?php

use App\Filament\Resources\ScanBatches\Pages\ListScanBatches;
use App\Filament\Resources\ScanBatches\Pages\ViewScanBatch;
use App\Filament\Resources\ScanResults\Pages\ListScanResults;
use App\Filament\Resources\ScanResults\Pages\ViewScanResult;
use App\Models\ExpertRule;
use App\Models\ScanBatch;
use App\Models\ScanResult;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
});

it('filters the result table to attacked traffic only', function () {
    $rule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);
    $attack = ScanResult::factory()->attack($rule)->create();
    $safe = ScanResult::factory()->create([
        'target_port' => 443,
    ]);

    Livewire::test(ListScanResults::class)
        ->assertCanSeeTableRecords([$attack, $safe])
        ->filterTable('attacks', true)
        ->assertCanSeeTableRecords([$attack])
        ->assertCanNotSeeTableRecords([$safe]);
});

it('shows one row per scan file and its details after opening the scan', function () {
    $batch = ScanBatch::factory()->create([
        'log_file_name' => 'Linux_2k.log',
        'total_results' => 1,
        'attack_count' => 1,
        'safe_count' => 0,
    ]);
    $result = ScanResult::factory()->create([
        'scan_batch_id' => $batch->id,
        'log_file_name' => 'Linux_2k.log',
    ]);

    Livewire::test(ListScanBatches::class)
        ->assertCanSeeTableRecords([$batch]);

    Livewire::test(ViewScanBatch::class, ['record' => $batch->getKey()]);

    expect($batch->results()->count())->toBe(1)
        ->and($result->scan_batch_id)->toBe($batch->id);
});

it('opens an analysis result with explanation evidence and mitigation', function () {
    $rule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
        'mitigation_recommendation' => 'Blokir IP dengan UFW.',
    ]);
    $result = ScanResult::factory()->attack($rule)->create([
        'raw_log_snippets' => ['Nov 05 09:57:00 server sshd: Failed password from 203.0.113.10 port 22'],
        'event_timestamp' => '2026-11-05 09:57:00',
        'fact_snapshot' => [
            'source_ip' => '203.0.113.10',
            'target_port' => 22,
            'failed_attempts' => 8,
        ],
        'explanation' => [
            'comparison' => 'Ditemukan 8 percobaan gagal dari IP 203.0.113.10 pada port 22.',
        ],
    ]);

    Livewire::test(ViewScanResult::class, [
        'record' => $result->getKey(),
    ])
        ->assertSuccessful()
        ->assertSee('Ditemukan 8 percobaan gagal')
        ->assertSee('Blokir IP dengan UFW.');
});
