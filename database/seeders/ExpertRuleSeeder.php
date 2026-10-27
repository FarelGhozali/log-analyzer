<?php

namespace Database\Seeders;

use App\Enums\ThreatLevel;
use App\Models\ExpertRule;
use Illuminate\Database\Seeder;

class ExpertRuleSeeder extends Seeder
{
    /**
     * Seed the default knowledge base.
     */
    public function run(): void
    {
        $rules = [
            [
                'rule_name' => 'Deteksi Brute Force SSH',
                'target_port' => 22,
                'min_failed_attempts' => 5,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Serangan Brute Force SSH terdeteksi!',
            ],
            [
                'rule_name' => 'Deteksi Serangan FTP',
                'target_port' => 21,
                'min_failed_attempts' => 5,
                'threat_level' => ThreatLevel::High,
                'conclusion_message' => 'Upaya akses paksa FTP terdeteksi!',
            ],
            [
                'rule_name' => 'Deteksi Login Web Berulang HTTP',
                'target_port' => 80,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Medium,
                'conclusion_message' => 'Percobaan login HTTP berulang terdeteksi!',
            ],
            [
                'rule_name' => 'Deteksi Login Web Berulang HTTPS',
                'target_port' => 443,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Medium,
                'conclusion_message' => 'Percobaan login HTTPS berulang terdeteksi!',
            ],
            [
                'rule_name' => 'Deteksi Akses Paksa Database MySQL',
                'target_port' => 3306,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Upaya akses paksa database MySQL terdeteksi!',
            ],
        ];

        foreach ($rules as $rule) {
            ExpertRule::query()->updateOrCreate(
                ['rule_name' => $rule['rule_name']],
                $rule,
            );
        }
    }
}
