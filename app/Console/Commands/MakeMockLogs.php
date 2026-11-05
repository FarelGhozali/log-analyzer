<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Illuminate\Console\Command;
use RuntimeException;

class MakeMockLogs extends Command
{
    protected $signature = 'make:mock-logs
                            {type : The log type to generate (ssh or web)}
                            {--lines=100 : The number of log lines to generate}';

    protected $description = 'Generate mock Linux SSH or web server logs';

    public function handle(): int
    {
        $type = strtolower((string) $this->argument('type'));
        $lines = filter_var($this->option('lines'), FILTER_VALIDATE_INT);

        if (! in_array($type, ['ssh', 'web'], true)) {
            $this->error('The type must be either "ssh" or "web".');

            return self::FAILURE;
        }

        if ($lines === false || $lines < 15) {
            $this->error('The --lines option must be an integer of at least 15.');

            return self::FAILURE;
        }

        $path = storage_path('logs/mock_'.$type.'.log');
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open {$path} for writing.");
        }

        try {
            $this->writeLines($handle, $type, $lines);
        } finally {
            fclose($handle);
        }

        $this->info("Generated {$lines} {$type} log lines in {$path}.");

        return self::SUCCESS;
    }

    /**
     * @param  resource  $handle
     */
    private function writeLines(mixed $handle, string $type, int $lines): void
    {
        $burstStart = random_int(0, $lines - 15);
        $burstIp = $this->randomIp();
        $baseTime = new DateTimeImmutable('-'.random_int(0, 7).' days');

        for ($lineNumber = 0; $lineNumber < $lines; $lineNumber++) {
            if ($lineNumber >= $burstStart && $lineNumber < $burstStart + 15) {
                $timestamp = $baseTime->setTime(
                    (int) $baseTime->format('H'),
                    (int) $baseTime->format('i'),
                    $lineNumber - $burstStart,
                );

                $line = $type === 'ssh'
                    ? $this->sshBurstLine($timestamp, $burstIp)
                    : $this->webBurstLine($timestamp, $burstIp);
            } else {
                $timestamp = $baseTime->modify('+'.random_int(0, 3600).' seconds');
                $line = $type === 'ssh'
                    ? $this->sshLine($timestamp)
                    : $this->webLine($timestamp);
            }

            if (fwrite($handle, $line.PHP_EOL) === false) {
                throw new RuntimeException('Unable to write the generated log line.');
            }
        }
    }

    private function sshLine(DateTimeImmutable $timestamp): string
    {
        $ip = $this->randomIp();
        $user = ['admin', 'test', 'oracle'][random_int(0, 2)];

        return sprintf(
            '%s server sshd[%d]: %s',
            $timestamp->format('M d H:i:s'),
            random_int(1000, 65000),
            random_int(0, 1) === 1
                ? "Accepted publickey for deploy from {$ip} port 22 ssh2"
                : "Failed password for invalid user {$user} from {$ip} port 22 ssh2",
        );
    }

    private function sshBurstLine(DateTimeImmutable $timestamp, string $ip): string
    {
        return sprintf(
            '%s server sshd[%d]: Failed password for invalid user admin from %s port 22 ssh2',
            $timestamp->format('M d H:i:s'),
            random_int(1000, 65000),
            $ip,
        );
    }

    private function webLine(DateTimeImmutable $timestamp): string
    {
        $paths = ['/', '/login', '/admin', '/api/status', '/wp-login.php'];
        $methods = ['GET', 'GET', 'GET', 'POST'];
        $status = random_int(0, 4) === 0 ? 404 : 200;

        return $this->combinedWebLine(
            $timestamp,
            $this->randomIp(),
            $methods[array_rand($methods)],
            $paths[array_rand($paths)],
            $status,
        );
    }

    private function webBurstLine(DateTimeImmutable $timestamp, string $ip): string
    {
        return $this->combinedWebLine($timestamp, $ip, 'POST', '/login', 401);
    }

    private function combinedWebLine(
        DateTimeImmutable $timestamp,
        string $ip,
        string $method,
        string $path,
        int $status,
    ): string {
        return sprintf(
            '%s - - [%s +0000] "%s %s HTTP/1.1" %d %d "-" "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36"',
            $ip,
            $timestamp->format('d/M/Y:H:i:s'),
            $method,
            $path,
            $status,
            random_int(100, 5000),
        );
    }

    private function randomIp(): string
    {
        return sprintf(
            '%d.%d.%d.%d',
            random_int(1, 223),
            random_int(0, 255),
            random_int(0, 255),
            random_int(1, 254),
        );
    }
}
