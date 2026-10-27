<?php

use App\Enums\ThreatLevel;
use App\Filament\Resources\ExpertRules\Pages\CreateExpertRule;
use App\Models\ExpertRule;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
});

it('creates an expert rule from the admin panel', function () {
    Livewire::test(CreateExpertRule::class)
        ->fillForm([
            'rule_name' => 'Deteksi Brute Force SSH',
            'target_port' => 22,
            'min_failed_attempts' => 5,
            'threat_level' => ThreatLevel::Critical->value,
            'conclusion_message' => 'Serangan Brute Force SSH terdeteksi!',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('expert_rules', [
        'rule_name' => 'Deteksi Brute Force SSH',
        'target_port' => 22,
        'min_failed_attempts' => 5,
        'threat_level' => ThreatLevel::Critical->value,
        'conclusion_message' => 'Serangan Brute Force SSH terdeteksi!',
    ]);
});

it('rejects a target port outside the valid port range', function (int $port) {
    Livewire::test(CreateExpertRule::class)
        ->fillForm([
            'rule_name' => 'Deteksi Port Tidak Valid',
            'target_port' => $port,
            'min_failed_attempts' => 5,
            'threat_level' => ThreatLevel::High->value,
            'conclusion_message' => 'Port tidak valid.',
        ])
        ->call('create')
        ->assertHasFormErrors(['target_port']);

    expect(ExpertRule::query()->count())->toBe(0);
})->with([0, 65536]);
