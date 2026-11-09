<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $rule_name
 * @property int $target_port
 * @property int $min_failed_attempts
 * @property \App\Enums\ThreatLevel $threat_level
 * @property string $conclusion_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $advanced_conditions
 * @property string|null $mitigation_recommendation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanResult> $scanResults
 * @property-read int|null $scan_results_count
 * @method static \Database\Factories\ExpertRuleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereAdvancedConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereConclusionMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereMinFailedAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereMitigationRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereRuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereTargetPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereThreatLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpertRule whereUpdatedAt($value)
 */
	class ExpertRule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $log_file_name
 * @property int $total_results
 * @property int $attack_count
 * @property int $safe_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanResult> $results
 * @property-read int|null $results_count
 * @method static \Database\Factories\ScanBatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereAttackCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereLogFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereSafeCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereTotalResults($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanBatch whereUpdatedAt($value)
 */
	class ScanBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $source_ip
 * @property int $target_port
 * @property int $failed_attempts
 * @property \App\Enums\ScanStatus $status
 * @property int|null $triggered_rule_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $log_file_name
 * @property int|null $scan_batch_id
 * @property array<array-key, mixed>|null $raw_log_snippets
 * @property \Illuminate\Support\Carbon|null $event_timestamp
 * @property array<array-key, mixed>|null $fact_snapshot
 * @property array<array-key, mixed>|null $explanation
 * @property-read \App\Models\ScanBatch|null $scanBatch
 * @property-read \App\Models\ExpertRule|null $triggeredRule
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult attacks()
 * @method static \Database\Factories\ScanResultFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereEventTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereExplanation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereFactSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereFailedAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereLogFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereRawLogSnippets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereScanBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereSourceIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereTargetPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereTriggeredRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanResult whereUpdatedAt($value)
 */
	class ScanResult extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

