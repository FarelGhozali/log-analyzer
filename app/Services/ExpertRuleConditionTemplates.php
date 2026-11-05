<?php

namespace App\Services;

class ExpertRuleConditionTemplates
{
    /**
     * @return array<string, array{label: string, conditions: array<string, mixed>}>
     */
    public static function all(): array
    {
        return [
            'ssh_bruteforce' => [
                'label' => 'SSH brute force',
                'conditions' => [
                    'all' => [
                        ['field' => 'target_port', 'operator' => 'equals', 'value' => 22],
                        ['field' => 'failed_attempts', 'operator' => 'greater_than_or_equal', 'value' => 5],
                    ],
                ],
            ],
            'web_scan' => [
                'label' => 'Web scan / suspicious URI',
                'conditions' => [
                    'all' => [
                        ['field' => 'target_port', 'operator' => 'in', 'value' => [80, 443]],
                        ['field' => 'uri', 'operator' => 'regex', 'value' => '/(?:wp-admin|\\.env|\\.git|phpmyadmin)/i'],
                    ],
                ],
            ],
            'sql_injection' => [
                'label' => 'SQL injection',
                'conditions' => [
                    'field' => 'message',
                    'operator' => 'regex',
                    'value' => '/(?:union\\s+select|or\\s+1\\s*=\\s*1|select\\s+.*\\s+from)/i',
                ],
            ],
            'port_scan' => [
                'label' => 'Port scanning',
                'conditions' => [
                    'all' => [
                        ['field' => 'distinct_target_ports', 'operator' => 'greater_than_or_equal', 'value' => 10],
                        ['field' => 'failed_attempts', 'operator' => 'greater_than_or_equal', 'value' => 1],
                    ],
                ],
            ],
            'time_window' => [
                'label' => 'Serangan dalam rentang waktu',
                'conditions' => [
                    'field' => 'timestamp',
                    'operator' => 'between',
                    'value' => ['2026-01-01T00:00:00Z', '2026-01-01T23:59:59Z'],
                ],
            ],
            'log_file_filter' => [
                'label' => 'Filter file log tertentu',
                'conditions' => [
                    'field' => 'log_file',
                    'operator' => 'in',
                    'value' => ['auth.log', 'secure.log'],
                ],
            ],
            'regex_pattern' => [
                'label' => 'Regex pola log',
                'conditions' => [
                    'field' => 'message',
                    'operator' => 'regex',
                    'value' => '/failed\\s+(?:password|login|authentication)/i',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (array $template, string $key): array => [$key => $template['label']])
            ->all();
    }

    public static function json(string $key): string
    {
        return json_encode(
            self::all()[$key]['conditions'] ?? [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ) ?: '{}';
    }
}
