<?php

namespace App\Services;

use App\Exceptions\InvalidLogFileException;
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
     * @return list<array{source_ip: string, target_port: int, failed_attempts: int}>
     */
    public function parse(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidLogFileException('File log tidak dapat dibaca.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidLogFileException('File log tidak dapat dibaca.');
        }

        $contents = trim(preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? '');

        if ($contents === '') {
            throw new InvalidLogFileException('File log kosong.');
        }

        if (str_starts_with(ltrim($contents), '[') || str_starts_with(ltrim($contents), '{')) {
            return $this->parseJson($contents);
        }

        return $this->parseDelimited($contents);
    }

    /**
     * @return list<array{source_ip: string, target_port: int, failed_attempts: int}>
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
     * @return list<array{source_ip: string, target_port: int, failed_attempts: int}>
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
            str_getcsv($lines[0], $delimiter),
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
            $values = str_getcsv($line, $delimiter);

            if ($hasHeader) {
                if (count($values) !== count($headers)) {
                    throw new InvalidLogFileException(
                        'Jumlah kolom pada baris ke-'.($startLine + $offset).' tidak sesuai dengan header.',
                    );
                }

                /** @var array<string, string> $row */
                $row = array_combine($headers, $values);
            } else {
                if (count($values) < 3) {
                    throw new InvalidLogFileException(
                        'Baris ke-'.($startLine + $offset).' harus memiliki minimal 3 kolom: IP, port, dan jumlah kegagalan.',
                    );
                }

                $row = [
                    'source_ip' => $values[0],
                    'target_port' => $values[1],
                    'failed_attempts' => $values[2],
                ];
            }

            $entries[] = $this->toEntry($row, 'baris ke-'.($startLine + $offset));
        }

        return $this->ensureNotEmpty($entries);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{source_ip: string, target_port: int, failed_attempts: int}
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

        return [
            'source_ip' => $sourceIp,
            'target_port' => (int) $targetPort,
            'failed_attempts' => (int) $failedAttempts,
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
