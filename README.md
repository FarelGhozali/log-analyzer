# IDS Log Analyzer

A web-based expert system for detecting cyber attacks from server logs. The application uses **forward chaining** to evaluate log facts against a configurable knowledge base of expert rules, then records each result as either `SAFE` or `ATTACK`.

Built with **Laravel 13** and **Filament 5**.

## Features

- **Dynamic knowledge base** — create, update, and delete expert rules from the admin panel without changing application code.
- **Forward chaining inference engine** — evaluates each log entry against the rules stored in the database.
- **CSV, JSON, and TXT log uploads** — accepts files up to 5 MB (configurable) and validates every row.
- **Analysis result table** — shows the source IP, target port, failed attempts, status, triggered rule, and analysis time. Attacked rows are highlighted in red.
- **Filtering** — filter results by status or show attacks only.
- **Dashboard analytics** — displays the number of rules, analyzed log rows, detected attacks, and safe traffic.
- **Authentication** — the admin panel is protected by Filament's login page.

## Requirements

- PHP 8.3 or newer
- Composer 2
- Node.js 20 or newer with npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

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

If you use MySQL or PostgreSQL, configure the `DB_*` variables in `.env` and create the database before running `php artisan migrate --seed`. You do not need to create `database/database.sqlite` in that case.

The application is served at `http://localhost:8000`, but the IDS interface lives inside the Filament admin panel. Open:

```text
http://localhost:8000/admin
```

The root URL `/` is the default Laravel welcome page and is not part of the IDS application.

## Default Login

The database seeder creates an administrator account from the values in `config/ids.php`:

```text
Email:    admin@ids.test
Password: password
```

Change these values before deploying to production:

```dotenv
IDS_ADMIN_NAME=Administrator
IDS_ADMIN_EMAIL=admin@ids.test
IDS_ADMIN_PASSWORD=password
```

Re-run `php artisan db:seed` after changing the values, or update the account directly from the database.

## Application Routes

| Method | URI                    | Description                                      |
| ------ | ---------------------- | ------------------------------------------------ |
| GET    | `/`                    | Default Laravel welcome page                     |
| GET    | `/admin/login`         | Administrator login                              |
| GET    | `/admin`               | Dashboard with statistics and latest results     |
| GET    | `/admin/log-analyzer`  | Upload a log file and run the inference engine   |
| GET    | `/admin/expert-rules`  | Manage the expert rule knowledge base            |
| GET    | `/admin/scan-results`  | View and filter all analysis results             |

## How Detection Works

1. The uploaded file is parsed into normalized fact entries: source IP, target port, and failed attempts.
2. The inference engine loads all expert rules from the `expert_rules` table.
3. Rules are evaluated from the strictest minimum failure count to the most lenient. The first rule whose `target_port` matches and whose `min_failed_attempts` is less than or equal to the log's failed attempts is triggered.
4. The log entry is marked `ATTACK` and linked to the triggered rule. If no rule matches, it is marked `SAFE`.
5. Every result is stored in the `scan_results` table and displayed on the dashboard and results page.

The business logic is separated from the UI:

- `app/Services/LogParser.php` — parses and validates log files.
- `app/Services/InferenceEngine.php` — performs forward chaining.
- `app/Actions/AnalyzeLogFile.php` — orchestrates parsing, inference, and persistence.

## Supported Log Files

Upload CSV, JSON, or TXT files. CSV files may include a header row or be headerless.

CSV with a header:

```csv
source_ip,target_port,failed_attempts
203.0.113.10,22,8
198.51.100.20,22,2
```

Headerless CSV (columns must be in this order: IP, port, failed attempts):

```csv
203.0.113.10,22,8
198.51.100.20,22,2
```

JSON:

```json
{
    "logs": [
        {
            "source_ip": "203.0.113.10",
            "target_port": 22,
            "failed_attempts": 8
        }
    ]
}
```

The parser accepts these column aliases:

| Field           | Accepted names                                                              |
| --------------- | --------------------------------------------------------------------------- |
| Source IP       | `source_ip`, `ip`, `src_ip`, `source`, `ip_address`, `src`                  |
| Target port     | `target_port`, `port`, `dst_port`, `destination_port`, `dport`               |
| Failed attempts | `failed_attempts`, `failed_count`, `failed_logins`, `failures`, `attempts`  |

A sample log file is available at `tests/Fixtures/logs/traffic.csv`.

Upload size is limited by `IDS_MAX_UPLOAD_KB` (default: `5120`, or 5 MB).

## Database Schema

### `expert_rules` — knowledge base

| Column                | Type              | Description                                  |
| --------------------- | ----------------- | -------------------------------------------- |
| `id`                  | bigint, PK        | Primary key                                  |
| `rule_name`           | string            | Rule name, e.g. "Deteksi Brute Force SSH"    |
| `target_port`         | unsigned smallint | Monitored destination port                   |
| `min_failed_attempts` | unsigned int      | Minimum failed attempts before triggering    |
| `threat_level`        | string            | `LOW`, `MEDIUM`, `HIGH`, or `CRITICAL`       |
| `conclusion_message`  | string            | Conclusion shown when the rule is triggered  |
| `created_at`          | timestamp         | Creation time                                |
| `updated_at`          | timestamp         | Last update time                             |

### `scan_results` — output log

| Column              | Type              | Description                                             |
| ------------------- | ----------------- | ------------------------------------------------------- |
| `id`                | bigint, PK        | Primary key                                             |
| `source_ip`         | string            | Source IP address from the log                          |
| `target_port`       | unsigned smallint | Destination port from the log                           |
| `failed_attempts`   | unsigned int      | Failed attempts from the log                            |
| `status`            | string            | `SAFE` or `ATTACK`                                      |
| `triggered_rule_id` | bigint, FK, null  | References `expert_rules.id`; null when no rule matches |
| `created_at`        | timestamp         | Analysis time                                           |
| `updated_at`        | timestamp         | Last update time                                        |

## Project Structure

```text
app/
├── Actions/
│   └── AnalyzeLogFile.php
├── Data/
│   └── ScanSummary.php
├── Enums/
│   ├── ScanStatus.php
│   └── ThreatLevel.php
├── Exceptions/
│   └── InvalidLogFileException.php
├── Filament/
│   ├── Pages/
│   │   └── LogAnalyzer.php
│   ├── Resources/
│   │   ├── ExpertRules/
│   │   └── ScanResults/
│   └── Widgets/
│       ├── LatestScanResults.php
│       └── ScanStatsOverview.php
├── Models/
│   ├── ExpertRule.php
│   └── ScanResult.php
└── Services/
    ├── InferenceEngine.php
    └── LogParser.php
```

## Testing

This project uses Pest.

```bash
php artisan test --compact
```

The test suite covers the log parser, inference engine, analysis action, admin panel access, rule creation and validation, result filtering, and the database seeder.

## Code Style

Format PHP files with Laravel Pint:

```bash
vendor/bin/pint --dirty --format agent
```

## Deployment Notes

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Configure production database credentials and set a strong `IDS_ADMIN_PASSWORD`.
3. Install dependencies and build assets:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm ci
   npm run build
   php artisan filament:assets
   php artisan optimize
   ```

4. Ensure `storage/` and `bootstrap/cache/` are writable by the web server.
5. Only create administrator accounts for trusted users. The panel has no public registration page.

## License

This application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
