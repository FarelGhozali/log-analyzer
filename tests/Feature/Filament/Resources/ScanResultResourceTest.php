<?php

use App\Filament\Resources\ScanResults\Pages\ListScanResults;
use App\Models\ExpertRule;
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
