<?php

use App\Filament\Resources\ScanBatches\Pages\ListScanBatches;
use App\Filament\Resources\ScanBatches\Pages\ViewScanBatch;
use App\Filament\Resources\ScanResults\Pages\ListScanResults;
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
