<?php

use App\Exceptions\InvalidLogFileException;
use App\Services\LogParser;

it('parses csv log files into normalized entries', function () {
    $entries = (new LogParser)->parse(fixturePath('logs/traffic.csv'));

    expect($entries)->toBe([
        ['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 8],
        ['source_ip' => '198.51.100.20', 'target_port' => 22, 'failed_attempts' => 2],
        ['source_ip' => '192.0.2.30', 'target_port' => 443, 'failed_attempts' => 12],
        ['source_ip' => '192.0.2.40', 'target_port' => 3306, 'failed_attempts' => 15],
    ]);
});

it('parses csv headers written with alternate column names', function () {
    $entries = (new LogParser)->parse(fixturePath('logs/traffic-aliases.csv'));

    expect($entries)->toBe([
        ['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 8],
        ['source_ip' => '198.51.100.20', 'target_port' => 22, 'failed_attempts' => 2],
    ]);
});

it('parses json log files with mixed column names', function () {
    $entries = (new LogParser)->parse(fixturePath('logs/traffic.json'));

    expect($entries)->toBe([
        ['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 6],
        ['source_ip' => '198.51.100.20', 'target_port' => 443, 'failed_attempts' => 9],
    ]);
});

it('parses csv files that do not have a header row', function () {
    $entries = (new LogParser)->parse(fixturePath('logs/traffic-headerless.csv'));

    expect($entries)->toBe([
        ['source_ip' => '203.0.113.10', 'target_port' => 22, 'failed_attempts' => 6],
        ['source_ip' => '198.51.100.20', 'target_port' => 443, 'failed_attempts' => 9],
    ]);
});

it('parses SSH log files into normalized entries', function () {
    $entries = (new LogParser)->parse(fixturePath('logs/ssh.log'));

    expect($entries)->toHaveCount(1)
        ->and($entries[0]['source_ip'])->toBe('201.43.187.77')
        ->and($entries[0]['target_port'])->toBe(22)
        ->and($entries[0]['failed_attempts'])->toBe(3);
});

it('rejects a log row with an invalid ip address', function () {
    (new LogParser)->parse(fixturePath('logs/invalid-ip.csv'));
})->throws(InvalidLogFileException::class, 'Alamat IP tidak valid pada baris ke-2.');

it('rejects a header that is missing required columns', function () {
    (new LogParser)->parse(fixturePath('logs/missing-columns.csv'));
})->throws(InvalidLogFileException::class, 'Header file log harus memuat kolom IP, target port, dan jumlah kegagalan.');
