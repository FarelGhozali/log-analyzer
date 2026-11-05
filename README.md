# IDS Log Analyzer

## Abstract

IDS Log Analyzer is a Laravel-based expert system for classifying server log events as `SAFE` or `ATTACK`. It demonstrates Artificial Intelligence concepts through fact normalization, a database-backed knowledge base, and deterministic Forward Chaining inference.

The application supports two rule modes:

1. **Basic Mode** — evaluates target ports and failed-attempt thresholds through the existing GUI fields.
2. **Advanced Mode** — evaluates JSON conditions, regular expressions, source correlation, and time-window thresholds.

The project is intended for academic evaluation, controlled experimentation, and development of rule-based intrusion-detection workflows.

## Technology Stack

| Component | Technology |
|---|---|
| Backend | Laravel 13 |
| Language | PHP 8.3 or newer |
| Administration panel | Filament 5 |
| Database | SQLite by default; PostgreSQL-compatible configuration supported |
| Containerization | Docker or Podman |
| Web server | Nginx with PHP-FPM |
| Testing | Pest |
| Inference strategy | Forward Chaining |

## System Architecture

```text
Log File
   |
   v
LogParser
   |
   v
Normalized Facts
   |
   v
InferenceEngine <---- ExpertRule Knowledge Base
   |
   v
Threat Classification
   |
   v
ScanResult Persistence
   |
   v
Filament Dashboard
```

### Core Components

| Component | Responsibility |
|---|---|
| `LogParser` | Parses and validates CSV, JSON, or delimited log data |
| `ExpertRule` | Stores the rule knowledge base |
| `InferenceEngine` | Applies Forward Chaining to normalized facts |
| `AnalyzeLogFile` | Coordinates parsing, inference, and persistence |
| `ScanResult` | Stores each classification result |
| `MakeMockLogs` | Generates controlled SSH and web log samples |
| Filament Resources | Provides administrative interfaces |

Important implementation files:

```text
app/
├── Actions/AnalyzeLogFile.php
├── Console/Commands/MakeMockLogs.php
├── Filament/
├── Models/
│   ├── ExpertRule.php
│   └── ScanResult.php
└── Services/
    ├── ExpertRuleConditionTemplates.php
    ├── InferenceEngine.php
    └── LogParser.php
```

## Artificial Intelligence Logic

### Forward Chaining

The inference engine uses a data-driven Forward Chaining process:

1. Load expert rules from the database.
2. Parse the uploaded file.
3. Normalize each record into structured facts.
4. Evaluate facts against rule conditions.
5. Select the first matching rule according to rule ordering.
6. Classify the record as `ATTACK` or `SAFE`.
7. Persist the result.

For Basic Mode:

```text
log.target_port == rule.target_port
AND
log.failed_attempts >= rule.min_failed_attempts
```

The conclusions are deterministic. Identical facts and identical rules produce identical results. The engine does not train a statistical model or calculate probabilistic confidence.

### Fact Normalization

The minimum normalized structure is:

```json
{
    "source_ip": "203.0.113.10",
    "target_port": 22,
    "failed_attempts": 8
}
```

Additional JSON or CSV fields are retained for advanced evaluation:

```json
{
    "source_ip": "203.0.113.10",
    "target_port": 443,
    "failed_attempts": 1,
    "message": "Failed password for admin",
    "uri": "/login",
    "timestamp": "2026-11-10T10:04:00Z",
    "log_file": "auth.log"
}
```

Supported aliases include:

| Normalized field | Accepted aliases |
|---|---|
| `source_ip` | `source_ip`, `ip`, `src_ip`, `source`, `ip_address`, `src` |
| `target_port` | `target_port`, `port`, `dst_port`, `destination_port`, `dport` |
| `failed_attempts` | `failed_attempts`, `failed_count`, `failed_logins`, `failures`, `attempts`, `failed` |

## Rule Engine

### Basic Mode

Basic Mode preserves the original GUI fields:

- `target_port`
- `min_failed_attempts`
- `threat_level`
- `conclusion_message`

Example:

```text
Target port: 22
Minimum failed attempts: 5
```

A record with port `22` and at least five failed attempts triggers the rule.

Rules are ordered by the highest `min_failed_attempts` first, followed by the lowest rule ID. This causes stricter thresholds to take precedence.

### Advanced Mode

Advanced Mode is active when `advanced_conditions` contains JSON. The JSON evaluator supports regular expressions, thresholds, source-IP correlation, and time windows.

Primary format:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+password/i",
    "time_window_minutes": 5,
    "threshold": 5
}
```

| Property | Type | Description |
|---|---|---|
| `match_type` | string | Currently supported value: `regex` |
| `pattern` | string | PHP-compatible regular expression |
| `time_window_minutes` | integer | Duration of the evaluation window |
| `threshold` | integer | Minimum matching events required |

For time-window rules, matching records are counted for the same `source_ip`. The current record timestamp is the end of the window. Records without valid timestamps do not satisfy a time-window rule.

Timestamp example:

```text
2026-11-10T10:04:00Z
```

### Advanced Condition Format

The application also supports `all`, `any`, and field operators.

```json
{
    "all": [
        {
            "field": "target_port",
            "operator": "in",
            "value": [80, 443]
        },
        {
            "field": "uri",
            "operator": "regex",
            "value": "/(?:wp-admin|\\.env|\\.git)/i"
        }
    ]
}
```

Supported operators:

| Operator | Meaning |
|---|---|
| `equals` | Exact or numeric equality |
| `not_equals` | Different value |
| `contains` | Case-insensitive substring search |
| `regex` | Regular expression matching |
| `in` | Value exists in an array |
| `greater_than` | Numeric greater-than comparison |
| `greater_than_or_equal` | Numeric greater-than-or-equal comparison |
| `less_than` | Numeric less-than comparison |
| `less_than_or_equal` | Numeric less-than-or-equal comparison |
| `between` | Value lies between two boundaries |

## Installation and Deployment

### Containerized Deployment

Requirements:

- Docker Engine with Docker Compose, or Podman with Compose support.
- TCP port `9000`, unless overridden.

Start the application:

```bash
docker-compose up -d
```

For Podman:

```bash
podman compose up -d
```

Check service status:

```bash
docker-compose ps
```

The application is available at:

```text
http://localhost:9000
```

The Filament panel is available at:

```text
http://localhost:9000/admin
```

View logs:

```bash
docker-compose logs -f app
docker-compose logs -f web
```

Run migrations:

```bash
docker-compose exec app php artisan migrate --force
```

Stop the deployment:

```bash
docker-compose down
```

### Environment Configuration

The Compose deployment uses SQLite by default:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

Common configuration values:

```dotenv
APP_NAME=IDS Log Analyzer
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:9000
APP_TIMEZONE=UTC
APP_PORT=9000

IDS_ADMIN_NAME=Administrator
IDS_ADMIN_EMAIL=admin@example.test
IDS_ADMIN_PASSWORD=change-this-password
```

To use PostgreSQL, configure the Laravel database variables and provide a PostgreSQL service separately:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ids_log_analyzer
DB_USERNAME=ids_user
DB_PASSWORD=change-this-password
```

### Native Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The native development panel is available at `http://localhost:8000/admin`.

## Usage Guide

### Create a Basic Rule

1. Open `/admin`.
2. Navigate to **Aturan Pakar**.
3. Select **Create**.
4. Open **Pengaturan Dasar**.
5. Enter the rule name, target port, minimum failed attempts, threat level, and conclusion message.
6. Save the rule.

Example:

```text
Rule name: SSH Brute Force
Target port: 22
Minimum failed attempts: 5
Threat level: HIGH
Conclusion: Repeated SSH authentication failures detected.
```

### Create an Advanced Rule

1. Open **Aturan Pakar**.
2. Select **Create** or edit an existing rule.
3. Open **Pengaturan Lanjutan**.
4. Select a template or enter JSON manually.
5. Save the rule.

SSH authentication burst:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+password/i",
    "time_window_minutes": 5,
    "threshold": 5
}
```

SQL injection pattern:

```json
{
    "match_type": "regex",
    "pattern": "/(?:union\\s+select|or\\s+1\\s*=\\s*1|select\\s+.*\\s+from)/i",
    "threshold": 1
}
```

Suspicious web resource:

```json
{
    "all": [
        {
            "field": "target_port",
            "operator": "in",
            "value": [80, 443]
        },
        {
            "field": "uri",
            "operator": "regex",
            "value": "/(?:wp-admin|\\.env|\\.git|phpmyadmin)/i"
        }
    ]
}
```

An advanced rule referencing `message`, `uri`, or `timestamp` requires those fields to exist in the uploaded facts.

### Upload Logs

CSV:

```csv
source_ip,target_port,failed_attempts,message,timestamp
203.0.113.10,22,1,Failed password for admin,2026-11-10T10:00:00Z
203.0.113.10,22,1,Failed password for root,2026-11-10T10:02:00Z
203.0.113.10,22,1,Failed password for test,2026-11-10T10:04:00Z
```

JSON:

```json
{
    "logs": [
        {
            "source_ip": "203.0.113.10",
            "target_port": 22,
            "failed_attempts": 1,
            "message": "Failed password for admin",
            "timestamp": "2026-11-10T10:00:00Z"
        }
    ]
}
```

The Log Analyzer page parses the file, evaluates the facts, persists `scan_results`, and displays the classifications.

## Simulation and Testing

The custom Artisan command generates controlled SSH or web log samples.

Generate SSH logs:

```bash
docker-compose exec app php artisan make:mock-logs ssh --lines=100
```

Generate web logs:

```bash
docker-compose exec app php artisan make:mock-logs web --lines=100
```

The files are written to:

```text
storage/logs/mock_ssh.log
storage/logs/mock_web.log
```

The command accepts `ssh` or `web` and requires at least 15 lines:

```bash
php artisan make:mock-logs ssh --lines=50
php artisan make:mock-logs web --lines=250
```

Inspect generated output:

```bash
docker-compose exec app sh -c 'tail -n 20 storage/logs/mock_ssh.log'
```

The generated raw SSH and web logs are simulation artifacts. For direct upload, use parser-compatible CSV or JSON containing the normalized fields required by the selected rule.

Run the complete test suite:

```bash
docker-compose exec app php artisan test --compact
```

Run targeted tests:

```bash
docker-compose exec app php artisan test --compact tests/Feature/Services/InferenceEngineTest.php
docker-compose exec app php artisan test --compact tests/Feature/Services/LogParserTest.php
docker-compose exec app php artisan test --compact tests/Feature/Actions/AnalyzeLogFileTest.php
```

The tests cover parser validation, basic inference, advanced regular expressions, time-window thresholds, safe classifications, persistence, and Filament resource behavior.

## Database Schema

### `expert_rules`

| Column | Type | Description |
|---|---|---|
| `id` | bigint | Primary key |
| `rule_name` | string | Rule name |
| `target_port` | unsigned small integer | Basic-mode destination port |
| `min_failed_attempts` | unsigned integer | Basic-mode threshold |
| `threat_level` | string | Threat level |
| `conclusion_message` | string | Rule conclusion |
| `advanced_conditions` | nullable JSON | Advanced-mode conditions |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Update timestamp |

### `scan_results`

| Column | Type | Description |
|---|---|---|
| `id` | bigint | Primary key |
| `source_ip` | string | Source address |
| `target_port` | unsigned small integer | Destination port |
| `failed_attempts` | unsigned integer | Failed attempts |
| `status` | string | `SAFE` or `ATTACK` |
| `triggered_rule_id` | nullable foreign key | Matching expert rule |
| `created_at` | timestamp | Analysis timestamp |
| `updated_at` | timestamp | Update timestamp |

## Application Routes

| URI | Purpose |
|---|---|
| `/admin/login` | Administrator authentication |
| `/admin` | Dashboard |
| `/admin/log-analyzer` | Log upload and analysis |
| `/admin/expert-rules` | Expert rule management |
| `/admin/scan-results` | Analysis result management |

## Development Commands

```bash
php artisan migrate
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
composer run dev
```

## Limitations

- The system is rule-based and does not train a statistical model.
- Detection quality depends on the configured rules and input-field quality.
- Advanced patterns must be valid PHP-compatible regular expressions.
- Time-window rules require valid timestamps.
- Missing referenced fields cause an advanced condition to fail.
- The default Compose deployment uses SQLite and does not provision PostgreSQL automatically.
- Generated mock logs are controlled simulation data and are not production telemetry.

## Academic Scope

This project demonstrates:

- Knowledge representation through expert rules.
- Fact normalization from semi-structured logs.
- Deterministic Forward Chaining.
- Dual-mode rule evaluation.
- Separation of inference logic from presentation and persistence layers.

