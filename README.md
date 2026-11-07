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
| `LogParser` | Parses and validates CSV, JSON, SSH/syslog, Apache access, or delimited log data |
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

### How to Construct a Custom JSON Rule

The value stored in `advanced_conditions` must be one valid JSON object. Do not wrap the rule in a `logs` array; `logs` is only used for uploaded log files.

Construct a rule in this order:

1. Identify the fact that must be inspected, such as `message`, `uri`, `target_port`, or `timestamp`.
2. Select either the primary regex format or the structured condition format.
3. Select an operator that is compatible with the fact's data type.
4. Define the comparison value, regular expression, or value list.
5. Add a threshold and time window when multiple events must be counted.
6. Confirm that every referenced field exists in the uploaded log records.
7. Validate that the final content is valid JSON before saving it.

The JSON rule is entered in the **Kondisi Lanjutan** CodeEditor field. The template selector can populate an initial example, but the resulting JSON can be edited manually.

#### Format A: Regex and Event Threshold

Use this format when a regular expression must be counted across log records:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+password/i",
    "threshold": 5
}
```

Field requirements:

| Field | Required | Valid value |
|---|---:|---|
| `match_type` | Yes | `"regex"` |
| `pattern` | Yes | A valid PHP-compatible regular expression |
| `threshold` | Yes | Positive integer |
| `time_window_minutes` | No | Integer greater than or equal to `0` |

The pattern is tested against every scalar value in each parsed log record. A record is counted when any scalar value matches the pattern.

For example, this rule counts failed authentication messages:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+(?:password|login|authentication)/i",
    "threshold": 3
}
```

#### Format B: Regex with a Time Window

Add `time_window_minutes` when matching events must occur within a limited period:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+password/i",
    "time_window_minutes": 5,
    "threshold": 5
}
```

The evaluator:

1. Uses the current record's `timestamp` as the end of the window.
2. Subtracts `time_window_minutes` to calculate the start.
3. Keeps records from the same `source_ip`.
4. Counts records whose timestamps fall inside the inclusive window.
5. Triggers the rule when the count is greater than or equal to `threshold`.

Every record used by a time-window rule must contain a parseable timestamp. ISO 8601 is recommended:

```json
{
    "timestamp": "2026-11-10T10:04:00Z"
}
```

Example input for the previous rule:

```json
{
    "logs": [
        {
            "source_ip": "203.0.113.10",
            "target_port": 22,
            "failed_attempts": 1,
            "message": "Failed password for admin",
            "timestamp": "2026-11-10T10:00:00Z"
        },
        {
            "source_ip": "203.0.113.10",
            "target_port": 22,
            "failed_attempts": 1,
            "message": "Failed password for root",
            "timestamp": "2026-11-10T10:02:00Z"
        },
        {
            "source_ip": "203.0.113.10",
            "target_port": 22,
            "failed_attempts": 1,
            "message": "Failed password for test",
            "timestamp": "2026-11-10T10:04:00Z"
        }
    ]
}
```

The first object is the rule JSON. The second object is an uploaded log file. They are separate inputs and must not be combined.

#### Format C: Single Structured Condition

Use a single condition when one field is sufficient:

```json
{
    "field": "message",
    "operator": "contains",
    "value": "failed password"
}
```

The `field` must match a key in the normalized log record. The `value` type should match the field:

```json
{
    "field": "target_port",
    "operator": "equals",
    "value": 22
}
```

```json
{
    "field": "log_file",
    "operator": "in",
    "value": ["auth.log", "secure.log"]
}
```

#### Format D: Multiple Conditions with `all`

Use `all` when every condition must be true:

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

#### Format E: Multiple Conditions with `any`

Use `any` when at least one condition must be true:

```json
{
    "any": [
        {
            "field": "message",
            "operator": "contains",
            "value": "union select"
        },
        {
            "field": "uri",
            "operator": "regex",
            "value": "/or\\s+1\\s*=\\s*1/i"
        }
    ]
}
```

### JSON Construction Rules

- Use double quotes for JSON keys and string values.
- Do not add a trailing comma after the last property or array element.
- Use JSON numbers for numeric comparisons, not quoted strings.
- Escape backslashes inside regex strings. For example, write `\\s` in JSON to produce the regex token `\s`.
- Enclose PHP regex patterns in delimiters such as `/.../i`.
- Use `threshold` values greater than or equal to `1`.
- Use non-negative values for `time_window_minutes`.
- Use `timestamp` values that can be parsed by Carbon.
- Use only fields present in the uploaded facts.
- An unknown operator or missing field causes the structured condition to fail.
- A malformed regex does not produce a match.
- An empty JSON object is accepted as an empty advanced condition, but it does not express a meaningful detection rule.

### Common Invalid JSON

Invalid because single quotes are not JSON syntax:

```text
{'field': 'target_port', 'operator': 'equals', 'value': 22}
```

Invalid because of the trailing comma:

```json
{
    "field": "target_port",
    "operator": "equals",
    "value": 22,
}
```

Invalid because the regular expression backslash is not escaped:

```json
{
    "match_type": "regex",
    "pattern": "/failed\s+password/i",
    "threshold": 1
}
```

Correct form:

```json
{
    "match_type": "regex",
    "pattern": "/failed\\s+password/i",
    "threshold": 1
}
```

### Important Interaction with Basic Fields

When `advanced_conditions` is `null`, the engine uses the existing Basic Mode evaluation based on `target_port` and `min_failed_attempts`.

When `advanced_conditions` contains JSON, the engine routes the rule to the Advanced Mode evaluator. The basic port and failed-attempt comparison is not automatically combined with that JSON object. If an advanced rule also needs a port restriction, include it in the structured JSON conditions:

```json
{
    "all": [
        {
            "field": "target_port",
            "operator": "equals",
            "value": 22
        },
        {
            "field": "message",
            "operator": "regex",
            "value": "/failed\\s+password/i"
        }
    ]
}
```

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

Run migrations and seed the database:

```bash
docker-compose exec app php artisan migrate --seed --force
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
IDS_ADMIN_EMAIL=admin@ids.test
IDS_ADMIN_PASSWORD=password
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

Default admin credentials configured in `.env.example`:
- **Email:** `admin@ids.test`
- **Password:** `password`

*(Note: If you customize `IDS_ADMIN_EMAIL` or `IDS_ADMIN_PASSWORD` in `.env`, run `php artisan db:seed` to update the admin account).*

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

The Log Analyzer accepts the following file formats:

| Format | Supported structure |
|---|---|
| `.csv` | Headered or headerless CSV containing source IP, target port, and failed attempts |
| `.json` | A log object, an array of log objects, or an object containing a `logs` array |
| `.log` / `.txt` | Linux SSH/syslog authentication lines or Apache/Nginx combined access-log lines |

#### SSH or Syslog `.log`

SSH log files can be uploaded directly. The parser extracts the source IP,
destination port, authentication result, original message, and timestamp.

```text
Nov 05 09:57:00 server sshd[5667]: Failed password for invalid user admin from 201.43.187.77 port 22 ssh2
Nov 05 09:57:01 server sshd[50675]: Failed password for invalid user admin from 201.43.187.77 port 22 ssh2
```

Each failed-password line produces `failed_attempts: 1`. Successful
`Accepted password` and `Accepted publickey` lines produce
`failed_attempts: 0`. Because syslog timestamps do not contain a year, the
current application year is used when normalizing them.

#### Apache or Nginx Combined `.log`

Combined access-log files can also be uploaded directly:

```text
192.0.2.10 - - [05/Nov/2026:09:57:00 +0000] "POST /login HTTP/1.1" 401 1024 "-" "Mozilla/5.0"
```

The parser extracts `source_ip`, `uri`, HTTP method, status code, timestamp,
and maps the request to target port `80`. Responses with status codes `400`
or higher produce `failed_attempts: 1`; successful responses produce
`failed_attempts: 0`.

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

The custom Artisan command is implemented in
[`app/Console/Commands/MakeMockLogs.php`](app/Console/Commands/MakeMockLogs.php).
It generates controlled Linux SSH authentication logs or Nginx/Apache-style
combined access logs for local parser and IDS testing. The command overwrites
the existing mock file for the selected type.

### Command Syntax

```text
php artisan make:mock-logs {type} {--lines=100}
```

| Argument or option | Allowed values | Description |
|---|---|---|
| `type` | `ssh`, `web` | Selects the log format to generate. |
| `--lines` | Integer, minimum `15` | Number of log lines to write. Defaults to `100`. |

### Native Installation

Run the command from the project root:

```bash
php artisan make:mock-logs ssh --lines=100
php artisan make:mock-logs web --lines=100
```

The generated files are available directly on the host at:

```text
storage/logs/mock_ssh.log
storage/logs/mock_web.log
```

### Docker or Podman Installation

Run the command inside the `app` container:

```bash
docker-compose exec app php artisan make:mock-logs ssh --lines=100
docker-compose exec app php artisan make:mock-logs web --lines=100
```

In the container, the files are stored at:

```text
/var/www/html/storage/logs/mock_ssh.log
/var/www/html/storage/logs/mock_web.log
```

The Docker Compose configuration stores `/var/www/html/storage` in the
persistent `storage_data` volume. Therefore, the generated files may not
appear in the repository's local `storage/logs` directory. Inspect them from
the container:

```bash
docker-compose exec app sh -c 'tail -n 20 /var/www/html/storage/logs/mock_ssh.log'
docker-compose exec app sh -c 'wc -l /var/www/html/storage/logs/mock_ssh.log'
```

To copy a generated file from the container to the host:

```bash
docker-compose cp app:/var/www/html/storage/logs/mock_ssh.log ./storage/logs/mock_ssh.log
```

### Generated Log Content

For `ssh`, the command generates standard `auth.log`-style entries, including
successful public-key authentication and failed passwords:

```text
Oct 27 13:22:15 server sshd[1234]: Failed password for invalid user admin from 192.168.1.100 port 22 ssh2
```

For `web`, it generates standard combined access-log entries:

```text
192.0.2.10 - - [27/Oct/2026:13:22:15 +0000] "GET /login HTTP/1.1" 200 1024 "-" "Mozilla/5.0 ..."
```

Every generated file contains a guaranteed burst sequence of at least
15 related events from one IP address within the same minute. For SSH, this is
15 failed `admin` login attempts; for web logs, this is 15 `POST /login`
requests returning HTTP `401`. This sequence is intended to trigger brute-force
or threshold-based IDS rules.

The command requires at least 15 lines so the burst sequence can always be
included:

```bash
php artisan make:mock-logs ssh --lines=15
php artisan make:mock-logs web --lines=250
```

The generated raw SSH and web logs are simulation artifacts. They are useful
for testing log formatting and detection rules, but they are not production
telemetry. They can be uploaded directly through the Log Analyzer as `.log`
files. Advanced rules still require the uploaded log records to contain the
fields referenced by the rule.

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
