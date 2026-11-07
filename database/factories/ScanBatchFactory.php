<?php

namespace Database\Factories;

use App\Models\ScanBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScanBatch>
 */
class ScanBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_file_name' => fake()->word().'.log',
            'total_results' => 10,
            'attack_count' => 3,
            'safe_count' => 7,
        ];
    }
}
