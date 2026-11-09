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
                'mitigation_recommendation' => 'Blokir IP sumber dengan UFW atau iptables dan aktifkan Fail2Ban untuk SSH.',
            ],
            [
                'rule_name' => 'Penyalahgunaan Akses Root via FTP',
                'target_port' => 21,
                'min_failed_attempts' => 1,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Akses root berisiko tinggi melalui layanan FTP terdeteksi!',
                'mitigation_recommendation' => 'Nonaktifkan login root dan akses anonymous pada vsftpd, lalu batasi FTP berdasarkan IP yang diizinkan.',
                'advanced_conditions' => [
                    'all' => [
                        ['field' => 'service_name', 'operator' => 'equals', 'value' => 'ftp'],
                        ['field' => 'username', 'operator' => 'equals', 'value' => 'root'],
                    ],
                ],
            ],
            [
                'rule_name' => 'Eksfiltrasi File Sensitif via FTP',
                'target_port' => 21,
                'min_failed_attempts' => 1,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Eksfiltrasi data atau konfigurasi sensitif via FTP terdeteksi!',
                'mitigation_recommendation' => 'Blokir transfer keluar yang tidak sah, batasi akses direktori sensitif, dan rotasi kredensial yang terekspos.',
                'advanced_conditions' => [
                    'all' => [
                        ['field' => 'direction', 'operator' => 'equals', 'value' => 'o'],
                        ['field' => 'filename', 'operator' => 'regex', 'value' => '/(?:^|\/)(?:etc\/(?:passwd|shadow)|root\/anaconda-ks\.cfg|\.ssh\/.*|\.env(?:$|\/.*)|[^\/]+\.(?:db|sql))$/i'],
                    ],
                ],
            ],
            [
                'rule_name' => 'Upload Malware via FTP',
                'target_port' => 21,
                'min_failed_attempts' => 1,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Injeksi file berbahaya atau staging malware via FTP terdeteksi!',
                'mitigation_recommendation' => 'Blokir upload ke direktori sistem, karantina file, dan lakukan pemindaian malware pada server.',
                'advanced_conditions' => [
                    'all' => [
                        ['field' => 'direction', 'operator' => 'equals', 'value' => 'i'],
                        ['field' => 'filename', 'operator' => 'regex', 'value' => '/(?:^\/(?:root|usr|etc|var\/www)\/|\\.(?:exe|dll|vbs|sh|php|py|so)$)/i'],
                    ],
                ],
            ],
            [
                'rule_name' => 'Transfer FTP Otomatis Berfrekuensi Tinggi',
                'target_port' => 21,
                'min_failed_attempts' => 1,
                'threat_level' => ThreatLevel::High,
                'conclusion_message' => 'Transfer FTP massal otomatis terdeteksi dalam rentang waktu singkat!',
                'mitigation_recommendation' => 'Terapkan rate limiting, batasi jumlah transfer per IP, dan blokir host yang melakukan burst mencurigakan.',
                'advanced_conditions' => [
                    'match_type' => 'ftp_burst',
                    'threshold' => 10,
                    'window_seconds' => 5,
                ],
            ],
            [
                'rule_name' => 'Deteksi Login Web Berulang HTTP',
                'target_port' => 80,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Medium,
                'conclusion_message' => 'Percobaan login HTTP berulang terdeteksi!',
                'mitigation_recommendation' => 'Aktifkan rate limiting, Fail2Ban, dan pembatasan akses endpoint login.',
            ],
            [
                'rule_name' => 'Deteksi Login Web Berulang HTTPS',
                'target_port' => 443,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Medium,
                'conclusion_message' => 'Percobaan login HTTPS berulang terdeteksi!',
                'mitigation_recommendation' => 'Aktifkan rate limiting, Fail2Ban, dan pembatasan akses endpoint login.',
            ],
            [
                'rule_name' => 'Deteksi Akses Paksa Database MySQL',
                'target_port' => 3306,
                'min_failed_attempts' => 10,
                'threat_level' => ThreatLevel::Critical,
                'conclusion_message' => 'Upaya akses paksa database MySQL terdeteksi!',
                'mitigation_recommendation' => 'Batasi port MySQL hanya untuk jaringan aplikasi dan blokir IP sumber yang mencurigakan.',
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
