# Totale Learning

AI-first, social-style Learning Experience Platform (LXP) built with Laravel + PostgreSQL + pgvector.

## Local Stack

- PHP 8.3+
- Laravel 12
- PostgreSQL 16 + `pgvector/pgvector:pg16` via Docker Compose

## Quick Start

```powershell
composer install
copy .env.example .env
php artisan key:generate
docker compose up -d
docker compose exec laravel.test php artisan migrate
```

## Validate Setup

Run these from the project root:

```powershell
docker compose ps
docker compose exec laravel.test php artisan db:show --database=pgsql --counts
docker compose exec pgsql psql -U sail -d laravel -c "SELECT extname FROM pg_extension WHERE extname = 'vector';"
docker compose exec pgsql psql -U sail -d laravel -c "SELECT to_regclass('public.learning_modules') AS learning_modules_table;"
```

## Docker Permissions Repair

If uploads fail with errors about being unable to create directories in `storage/app/private`, or Blade cache writes fail under `storage/framework/views`, repair the writable Laravel paths with:

```powershell
.\scripts\repair-laravel-permissions.ps1
```

That wrapper runs the in-container repair script at [scripts/repair-laravel-permissions.sh](/C:/LMS/totale-learning/scripts/repair-laravel-permissions.sh), which:

- recreates missing writable Laravel directories
- resets ownership to `sail:sail`
- restores group-write permissions
- clears compiled Laravel caches

You can also run the container script directly:

```powershell
docker compose exec -T laravel.test sh /var/www/html/scripts/repair-laravel-permissions.sh
```

## API: Similar Modules

Endpoint:

```text
GET /api/modules/similar/{id}?limit=10
```

Behavior:

- Uses pgvector cosine distance (`<=>`) against `learning_modules.embedding`
- Excludes the source module
- Orders nearest first
- `limit` default is `10`, allowed range is `1..50`

Example:

```powershell
curl "http://localhost/api/modules/similar/1?limit=10"
```

## API: Asset Upload + Ingestion (Phase 2)

### Run migrations

```powershell
docker compose exec laravel.test php artisan migrate
```

### Run queue worker

```powershell
docker compose exec laravel.test php artisan queue:work --tries=1 --timeout=120
```

### Install PDF text extraction library

The ingestion job uses `smalot/pdfparser` when available.

```powershell
docker compose exec laravel.test composer require smalot/pdfparser
```

### Upload a PDF asset

```powershell
curl -X POST "http://localhost/api/modules/1/assets" `
  -H "Accept: application/json" `
  -F "file=@C:\path\to\module.pdf;type=application/pdf"
```

### Start ingestion

```powershell
curl -X POST "http://localhost/api/assets/1/ingest" `
  -H "Accept: application/json"
```

## API: Social Feed (Phase 3)

Endpoint:

```text
GET /api/feed?limit=20
```

Behavior:

- Returns published feed posts only
- Ranks by engagement + recency score (`ranking_score`)
- `limit` default is `20`, allowed range is `1..50`

Example:

```powershell
curl "http://localhost/api/feed?limit=10"
```

## API: Mentor Threads + Messages (Phase 4)

Environment toggles:

```env
MENTOR_PROVIDER=local
MENTOR_PROVIDER_ENABLED=true
```

Endpoints:

```text
POST /api/mentor/threads
GET /api/mentor/threads/{id}?limit=50
POST /api/mentor/threads/{id}/messages
```

Examples:

```powershell
curl -X POST "http://localhost/api/mentor/threads" `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d "{\"learning_module_id\":1,\"title\":\"Module 1 Mentor\"}"

curl "http://localhost/api/mentor/threads/1?limit=50"

curl -X POST "http://localhost/api/mentor/threads/1/messages" `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -d "{\"content\":\"Explain vectors in algebra\"}"
```

## Ops + Governance (Phase 5)

Environment toggles:

```env
AI_OPS_RETENTION_DAYS=30
LEARNING_EVENTS_RETENTION_DAYS=90
MENTOR_TRACES_RETENTION_DAYS=90
```

Admin audit APIs:

```text
GET /api/admin/ai/usages?provider=&capability=&success=&from=&to=&page=&limit=
GET /api/admin/mentor/traces?thread_id=&module_id=&page=&limit=
GET /api/admin/ingestion/assets?status=&from=&to=&page=&limit=
```

Examples:

```powershell
curl "http://localhost/api/admin/ai/usages?provider=local&capability=mentor_answer&success=1&limit=20"
curl "http://localhost/api/admin/mentor/traces?module_id=1&limit=20"
curl "http://localhost/api/admin/ingestion/assets?status=ingested&limit=20"
```

Pruning command:

```powershell
docker compose exec laravel.test php artisan ops:prune
```

Scheduler:

- `ops:prune` is scheduled daily at `03:10` in `routes/console.php`.

## Tests

Run tests inside the Laravel container:

```powershell
docker compose exec laravel.test php artisan test --filter=VectorSearchServiceTest
docker compose exec laravel.test php artisan test --filter=SimilarModulesApiTest
docker compose exec laravel.test php artisan test --filter=LearningAssetApiTest
docker compose exec laravel.test php artisan test --filter=IngestLearningAssetTest
docker compose exec laravel.test php artisan test --filter=TextChunkerTest
docker compose exec laravel.test php artisan test --filter=SocialFeedServiceTest
docker compose exec laravel.test php artisan test --filter=FeedApiTest
docker compose exec laravel.test php artisan test --filter=MentorRetrievalServiceTest
docker compose exec laravel.test php artisan test --filter=MentorApiTest
docker compose exec laravel.test php artisan test --filter=AdminOpsApiTest
docker compose exec laravel.test php artisan test --filter=OpsPruneCommandTest
```

## Notes

- Keep secrets only in `.env`; do not commit keys.
- If you run `php artisan ...` directly from host PowerShell and `DB_HOST=pgsql`, database connection will fail because `pgsql` resolves only inside Docker. Use Docker-prefixed commands above, or set host-side `DB_HOST=127.0.0.1` in a separate local env strategy.
- Local-only default: no external telemetry provider is enabled; audit rows are stored only in your local project database.
- Mentor provider can be toggled with `MENTOR_PROVIDER_ENABLED=true|false`.
