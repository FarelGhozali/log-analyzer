<?php

namespace App\Data;

final readonly class ScanSummary
{
    public function __construct(
        public int $total,
        public int $attacks,
        public int $safe,
    ) {}
}
