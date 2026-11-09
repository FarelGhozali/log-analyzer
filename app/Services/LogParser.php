<?php

namespace App\Services;

use App\Exceptions\InvalidLogFileException;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Str;
use JsonException;

class LogParser
{
    /**
     * @var list<string>
     */
    private const IP_KEYS = ['source_ip', 'ip', 'src_ip', 'source', 'ip_address', 'src'];

    /**
     * @var list<string>
     */
    private const PORT_KEYS = ['target_port', 'port', 'dst_port', 'destination_port', 'dport'];

    /**
     * @var list<string>
     */
    private const FAILED_KEYS = ['failed_attempts', 'failed_count', 'failed_logins', 'failures', 'attempts', 'failed'];

    /**
     * Parse a CSV, JSON, or plain-text log file into normalized log entries.
     *
     * @return list<array<string, mixed>>
     */
    public function parse(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidLogFileException('File log tidak dapat dibaca.');
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new InvalidLogFileException('File log tidak dapat dibaca.');
        }

        $firstLine = fgets($handle);
        fclose($handle);

        if ($firstLine === false || trim(preg_replace('/^\xEF\xBB\xBF/', '', $firstLine) ?? '') === '') {
            throw new InvalidLogFileException('File log kosong.');
        }

        $firstLine = ltrim(preg_replace('/^\xEF\xBB\xBF/', '', $firstLine) ?? '');

        if (str_starts_with($firstLine, '[') || str_starts_with($firstLine, '{')) {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new InvalidLogFileException('File log tidak dapat dibaca.');
            }

            return $this->parseJson($contents);
        }

        if ($this->looksLikeDelimitedFile($firstLine)) {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new InvalidLogFileException('File log tidak dapat dibaca.');
            }

            return $this->parseDelimited($contents);
        }

        return $this->parsePlainText($path);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseJson(string $contents): array
    {
        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidLogFileException('File JSON tidak valid: '.$exception->getMessage());
        }

        if (is_array($data) && isset($data['logs']) && is_array($data['logs'])) {
            $data = $data['logs'];
        }

        if (is_array($data) && ! array_is_list($data)) {
            $data = [$data];
        }

        if (! is_array($data)) {
            throw new InvalidLogFileException('File JSON harus berisi array baris log.');
        }

        $entries = [];

        foreach (array_values($data) as $index => $row) {
            if (! is_array($row)) {
                throw new InvalidLogFileException('Baris ke-'.($index + 1).' pada file JSON bukan objek.');
            }

            $entries[] = $this->toEntry($this->normalizeKeys($row), 'baris ke-'.($index + 1));
        }

        return $this->ensureNotEmpty($entries);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseDelimited(string $contents): array
    {
        $lines = preg_split('/\R/', $contents) ?: [];
        $lines = array_values(array_filter($lines, fn (string $line): bool => trim($line) !== ''));

        if ($lines === []) {
            throw new InvalidLogFileException('File log tidak memiliki baris data yang dapat dianalisis.');
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $headers = array_map(
            fn (string $header): string => $this->normalizeKey($header),
            str_getcsv($lines[0], $delimiter, '"', '\\'),
        );
        $hasHeader = $this->hasKnownColumns($headers);

        if (! $hasHeader && $this->hasAnyKnownColumn($headers)) {
            throw new InvalidLogFileException('Header file log harus memuat kolom IP, target port, dan jumlah kegagalan.');
        }

        $startLine = $hasHeader ? 2 : 1;

        if ($hasHeader) {
            array_shift($lines);
        }

        $entries = [];

        foreach (array_values($lines) as $offset => $line) {
            $values = str_getcsv($line, $delimiter, '"', '\\');

            if ($hasHeader) {
                if (count($values) !== count($headers)) {
                    throw new InvalidLogFileException(
                        'Jumlah kolom pada baris ke-'.($startLine + $offset).' tidak sesuai dengan header.',
                    );
                }

                /** @var array<string, string> $row */
                $row = array_combine($headers, $values);
            } else {
                $row = [
                    'source_ip' => $values[0] ?? null,
                    'target_port' => $values[1] ?? null,
                    'failed_attempts' => $values[2] ?? null,
                ];
            }

            $row['raw_log'] = trim($line);
            $entries[] = $this->toEntry($row, 'baris ke-'.($startLine + $offset));
        }

        return $this->ensureNotEmpty($entries);
    }

    /**
     * Parse common SSH/syslog and Apache access log lines.
     *
     * @return list<array<string, mixed>>
     */
    private function parsePlainText(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new InvalidLogFileException('File log tidak dapat dibaca.');
        }

        $entries = [];
        $failedAttempts = [];

        while (($line = fgets($handle)) !== false) {
            if (trim($line) === '') {
                continue;
            }

            $entry = $this->parseXferlogLine($line)
                ?? $this->parseSshLogLine($line)
                ?? $this->parseApacheLogLine($line);

            if ($entry === null) {
                continue;
            }

            if (($entry['failed_attempts'] ?? 0) > 0 && ! isset($entry['service_name'])) {
                $key = $entry['source_ip'].'|'.$entry['target_port'];
                $failedAttempts[$key] = [
                    'source_ip' => $entry['source_ip'],
                    'target_port' => $entry['target_port'],
                    'failed_attempts' => ($failedAttempts[$key]['failed_attempts'] ?? 0) + 1,
                    'raw_log_snippets' => [
                        ...($failedAttempts[$key]['raw_log_snippets'] ?? []),
                        $entry['raw_log'],
                    ],
                    'timestamp' => $entry['timestamp'] ?? null,
                ];
            } else {
                $entries[] = $entry;
            }
        }

        fclose($handle);

        return $this->ensureNotEmpty([
            ...array_values($failedAttempts),
            ...$entries,
        ]);
    }

    /**
     * Parse a standard vsftpd xferlog line.
     *
     * @return array<string, mixed>|null
     */
    private function parseXferlogLine(string $line): ?array
    {
        $pattern = '/^(?<weekday>Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s+'
            .'(?<month>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+'
            .'(?<day>\d{1,2})\s+(?<time>\d{2}:\d{2}:\d{2})\s+(?<year>\d{4})\s+'
            .'(?<transfer_time>\d+)\s+(?<remote_host>\S+)\s+(?<file_size>\d+)\s+'
            .'(?<filename>.+?)\s+(?<transfer_type>[ab])\s+'
            .'(?<special_action_flag>[_CUT])\s+(?<direction>[oi])\s+'
            .'(?<access_mode>[rag])\s+(?<username>\S+)\s+(?<service_name>\S+)\s+'
            .'(?<authentication_method>\S+)\s+(?<authenticated_user_id>\S+)\s+'
            .'(?<completion_status>[ci])$/i';

        if (preg_match($pattern, trim($line), $matches) !== 1) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!D M j H:i:s Y',
            sprintf(
                '%s %s %s %s %s',
                $matches['weekday'],
                $matches['month'],
                $matches['day'],
                $matches['time'],
                $matches['year'],
            ),
            new DateTimeZone('UTC'),
        );

        if ($date === false) {
            throw new InvalidLogFileException('Timestamp xferlog tidak valid.');
        }

        $remoteHost = $this->normalizeRemoteHost($matches['remote_host']);

        return $this->toEntry([
            'source_ip' => $remoteHost,
            'target_port' => 21,
            'failed_attempts' => 0,
            'timestamp' => $date->format(DATE_ATOM),
            'transfer_time' => (int) $matches['transfer_time'],
            'remote_host' => $remoteHost,
            'file_size' => (int) $matches['file_size'],
            'filename' => $matches['filename'],
            'transfer_type' => strtolower($matches['transfer_type']),
            'special_action_flag' => $matches['special_action_flag'],
            'direction' => strtolower($matches['direction']),
            'access_mode' => strtolower($matches['access_mode']),
            'username' => $matches['username'],
            'service_name' => $matches['service_name'],
            'authentication_method' => $matches['authentication_method'],
            'authenticated_user_id' => $matches['authenticated_user_id'],
            'completion_status' => strtolower($matches['completion_status']),
            'raw_log' => trim($line),
        ], 'xferlog');
    }

    private function normalizeRemoteHost(string $remoteHost): string
    {
        $normalized = preg_replace('/^::ffff:/i', '', trim($remoteHost));

        return is_string($normalized) ? $normalized : trim($remoteHost);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseSshLogLine(string $line): ?array
    {
        $pattern = '/^(?<month>[A-Z][a-z]{2})\s+(?<day>\d{1,2})\s+(?<time>\d{2}:\d{2}:\d{2})\s+.+?'
            .'(?<message>Failed password|Accepted (?:password|publickey)).*?'
            .'(?:from\s+)?(?<source_ip>(?:\d{1,3}\.){3}\d{1,3})\s+port\s+(?<target_port>\d+)/i';
        $authenticationFailurePattern = '/^(?<month>[A-Z][a-z]{2})\s+(?<day>\d{1,2})\s+(?<time>\d{2}:\d{2}:\d{2})\s+.+?'
            .'authentication failure;.*?\brhost=(?<source_ip>(?:\d{1,3}\.){3}\d{1,3})\b/i';

        $matched = preg_match($pattern, trim($line), $matches) === 1;

        if (! $matched && preg_match($authenticationFailurePattern, trim($line), $matches) === 1) {
            $matches['message'] = 'Failed authentication';
            $matches['target_port'] = '22';
            $matched = true;
        }

        if (! $matched) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!M j H:i:s Y',
            sprintf('%s %s %s %s', $matches['month'], $matches['day'], $matches['time'], now()->year),
            new DateTimeZone('UTC'),
        );

        if ($date === false) {
            return null;
        }

        return $this->toEntry([
            'source_ip' => $matches['source_ip'],
            'target_port' => $matches['target_port'],
            'failed_attempts' => str_starts_with($matches['message'], 'Failed') ? 1 : 0,
            'message' => trim($line),
            'timestamp' => $date->format(DATE_ATOM),
            'log_file' => 'log',
            'raw_log' => trim($line),
        ], 'SSH log');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseApacheLogLine(string $line): ?array
    {
        $pattern = '/^(?<source_ip>(?:\d{1,3}\.){3}\d{1,3})\s+\S+\s+\S+\s+'
            .'\[(?<timestamp>[^\]]+)\]\s+"(?<method>[A-Z]+)\s+(?<uri>\S+)\s+[^"]+"\s+'
            .'(?<status>\d{3})\b/';

        if (preg_match($pattern, trim($line), $matches) !== 1) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!d/M/Y:H:i:s O',
            $matches['timestamp'],
            new DateTimeZone('UTC'),
        );

        if ($date === false) {
            return null;
        }

        return $this->toEntry([
            'source_ip' => $matches['source_ip'],
            'target_port' => 80,
            'failed_attempts' => (int) $matches['status'] >= 400 ? 1 : 0,
            'message' => trim($line),
            'method' => $matches['method'],
            'uri' => $matches['uri'],
            'status_code' => (int) $matches['status'],
            'timestamp' => $date->format(DATE_ATOM),
            'log_file' => 'log',
            'raw_log' => trim($line),
        ], 'Apache log');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function toEntry(array $row, string $label): array
    {
        $sourceIp = trim((string) $this->valueFor($row, self::IP_KEYS));
        $targetPort = trim((string) $this->valueFor($row, self::PORT_KEYS));
        $failedAttempts = trim((string) $this->valueFor($row, self::FAILED_KEYS));

        if ($sourceIp === '' || filter_var($sourceIp, FILTER_VALIDATE_IP) === false) {
            throw new InvalidLogFileException('Alamat IP tidak valid pada '.$label.'.');
        }

        if (! is_numeric($targetPort) || (int) $targetPort < 1 || (int) $targetPort > 65535) {
            throw new InvalidLogFileException('Target port tidak valid pada '.$label.'.');
        }

        if (! is_numeric($failedAttempts) || (int) $failedAttempts < 0) {
            throw new InvalidLogFileException('Jumlah kegagalan tidak valid pada '.$label.'.');
        }

        $rawLog = $row['raw_log'] ?? json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return [
            'source_ip' => $sourceIp,
            'target_port' => (int) $targetPort,
            'failed_attempts' => (int) $failedAttempts,
            'raw_log' => is_string($rawLog) ? $rawLog : '',
            ...array_diff_key($row, array_flip([
                ...self::IP_KEYS,
                ...self::PORT_KEYS,
                ...self::FAILED_KEYS,
                'raw_log',
            ])),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeKeys(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = $value;
        }

        return $normalized;
    }

    private function normalizeKey(string $key): string
    {
        $key = Str::snake(trim($key));

        return trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower($key)), '_');
    }

    private function looksLikeDelimitedFile(string $contents): bool
    {
        $firstLine = strtok($contents, "\r\n") ?: '';
        $delimiterCounts = [
            ',' => substr_count($firstLine, ','),
            ';' => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($delimiterCounts);
        $delimiter = array_key_first($delimiterCounts);

        if ($delimiter === null || $delimiterCounts[$delimiter] === 0) {
            return false;
        }

        $rawColumns = str_getcsv($firstLine, $delimiter, '"', '\\');
        $columns = array_map(
            fn (string $column): string => $this->normalizeKey($column),
            $rawColumns,
        );

        if ($this->hasAnyKnownColumn($columns)) {
            return true;
        }

        return count($rawColumns) >= 3
            && filter_var(trim($rawColumns[0]), FILTER_VALIDATE_IP) !== false
            && is_numeric(trim($rawColumns[1]))
            && is_numeric(trim($rawColumns[2]));
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($delimiters);

        return array_key_first($delimiters) ?? ',';
    }

    /**
     * @param  list<string>  $headers
     */
    private function hasKnownColumns(array $headers): bool
    {
        return $this->valueFor(array_flip($headers), self::IP_KEYS) !== null
            && $this->valueFor(array_flip($headers), self::PORT_KEYS) !== null
            && $this->valueFor(array_flip($headers), self::FAILED_KEYS) !== null;
    }

    /**
     * @param  list<string>  $headers
     */
    private function hasAnyKnownColumn(array $headers): bool
    {
        $flippedHeaders = array_flip($headers);

        return $this->valueFor($flippedHeaders, self::IP_KEYS) !== null
            || $this->valueFor($flippedHeaders, self::PORT_KEYS) !== null
            || $this->valueFor($flippedHeaders, self::FAILED_KEYS) !== null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $aliases
     */
    private function valueFor(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row)) {
                return $row[$alias];
            }
        }

        return null;
    }

    /**
     * @param  list<array{source_ip: string, target_port: int, failed_attempts: int}>  $entries
     * @return list<array{source_ip: string, target_port: int, failed_attempts: int}>
     */
    private function ensureNotEmpty(array $entries): array
    {
        if ($entries === []) {
            throw new InvalidLogFileException('File log tidak memiliki baris data yang dapat dianalisis.');
        }

        return $entries;
    }
}
