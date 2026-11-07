<?php

use App\Filament\Pages\LogAnalyzer;
use App\Models\ExpertRule;
use App\Models\ScanResult;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create());
});

it('analyzes an uploaded csv file and stores the results', function () {
    $rule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 5,
    ]);

    Livewire::test(LogAnalyzer::class)
        ->fillForm([
            'log_file' => UploadedFile::fake()->createWithContent(
                'traffic.csv',
                file_get_contents(fixturePath('logs/traffic.csv')),
            ),
        ])
        ->call('analyze')
        ->assertHasNoFormErrors()
        ->assertNotified('Analisis log selesai');

    expect(ScanResult::query()->count())->toBe(4)
        ->and(ScanResult::query()->attacks()->count())->toBe(1)
        ->and(ScanResult::query()->attacks()->first()?->triggered_rule_id)->toBe($rule->id);
});

it('analyzes an uploaded SSH log file and stores the results', function () {
    $rule = ExpertRule::factory()->forPort(22)->create([
        'min_failed_attempts' => 1,
    ]);

    Livewire::test(LogAnalyzer::class)
        ->fillForm([
            'log_file' => UploadedFile::fake()->createWithContent(
                'mock_ssh.log',
                file_get_contents(fixturePath('logs/ssh.log')),
            ),
        ])
        ->call('analyze')
        ->assertHasNoFormErrors()
        ->assertNotified('Analisis log selesai');

    expect(ScanResult::query()->count())->toBe(1)
        ->and(ScanResult::query()->first()?->log_file_name)->toBe('mock_ssh.log')
        ->and(ScanResult::query()->attacks()->count())->toBe(1)
        ->and(ScanResult::query()->attacks()->first()?->triggered_rule_id)->toBe($rule->id);
});

it('refuses to analyze a log when the knowledge base is empty', function () {
    Livewire::test(LogAnalyzer::class)
        ->fillForm([
            'log_file' => UploadedFile::fake()->createWithContent(
                'traffic.csv',
                file_get_contents(fixturePath('logs/traffic.csv')),
            ),
        ])
        ->call('analyze')
        ->assertNotified('Knowledge base masih kosong');

    expect(ScanResult::query()->count())->toBe(0);
});

it('shows an error notification when the uploaded log cannot be parsed', function () {
    ExpertRule::factory()->forPort(22)->create();

    Livewire::test(LogAnalyzer::class)
        ->fillForm([
            'log_file' => UploadedFile::fake()->createWithContent(
                'invalid-ip.csv',
                file_get_contents(fixturePath('logs/invalid-ip.csv')),
            ),
        ])
        ->call('analyze')
        ->assertNotified('Gagal menganalisis log');

    expect(ScanResult::query()->count())->toBe(0);
});
