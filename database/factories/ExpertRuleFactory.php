<?php

namespace Database\Factories;

use App\Enums\ThreatLevel;
use App\Models\ExpertRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpertRule>
 */
class ExpertRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rule_name' => 'Deteksi '.fake()->randomElement(['Brute Force SSH', 'FTP Flood', 'Login HTTP']),
            'target_port' => fake()->randomElement([21, 22, 80, 443, 3306]),
            'min_failed_attempts' => fake()->numberBetween(3, 10),
            'threat_level' => fake()->randomElement(ThreatLevel::cases()),
            'conclusion_message' => fake()->sentence(),
            'mitigation_recommendation' => fake()->sentence(),
        ];
    }

    public function forPort(int $port): static
    {
        return $this->state(fn (): array => [
            'target_port' => $port,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (): array => [
            'threat_level' => ThreatLevel::Critical,
        ]);
    }
}
