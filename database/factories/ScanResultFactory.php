<?php

namespace Database\Factories;

use App\Enums\ScanStatus;
use App\Models\ExpertRule;
use App\Models\ScanResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScanResult>
 */
class ScanResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_ip' => fake()->ipv4(),
            'target_port' => 22,
            'failed_attempts' => 1,
            'status' => ScanStatus::Safe,
            'triggered_rule_id' => null,
        ];
    }

    public function attack(ExpertRule $rule): static
    {
        return $this->state(fn (): array => [
            'target_port' => $rule->target_port,
            'failed_attempts' => $rule->min_failed_attempts,
            'status' => ScanStatus::Attack,
            'triggered_rule_id' => $rule->id,
        ]);
    }
}
