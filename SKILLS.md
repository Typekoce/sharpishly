# SKILLS.md – Sharpishly

This file describes the current technical and architectural skills embodied in the Sharpishly codebase.  
It serves as a living reference for contributors, future maintainers, and AI agents interacting with the project.

Last updated: March 06, 2026

## Core Language & Runtime

- **PHP 8.2+** (strict typing with `declare(strict_types=1)`)
- Composer autoloading (PSR-4 via `autoload.php`)
- No heavy frameworks — pure custom lightweight MVC

## Architecture & Design Patterns

- **Front Controller pattern** (`index.php` → `FrontController`)
- **Dependency-light MVC**:
  - Controllers in `src/Controllers/`
  - Models in `src/Models/` (data access + migration logic)
  - Services in `src/Services/` (CSVProcessor, VideoOptimizerService, Logger)
- **Single entry point** — all requests go through `index.php`
- **Manual routing** via URL segment parsing (`/php/home/migrate` → `HomeController::migrate()`)
- **Constructor-based dependency injection** (e.g. `new Db()` in models/controllers)

## Database & Persistence

- **MySQL 8+** via PDO (no ORM)
- Custom lightweight `Db` class:
  - `find()`, `save()`, `create()` (array-based schema)
  - Public `$pdo` property (direct access)
  - Escaping & prepared statements
- **File-based schema migrations** in `HomeModel::migrate()` (array → SQL)
- **Jobs table** for background processing (`jobs`, `csv_records`)
- **Vault** for AES-256-CBC encrypted secrets

## Background Processing

- **File-based queue** (`storage/queue/*.job` + atomic rename)
- **Single long-running worker daemon** (`worker-daemon.php`)
  - Polls queue every few seconds
  - Supports retry, dead-letter, done folders
  - Graceful shutdown (SIGTERM/SIGINT)
  - Dedicated CSV processing (`CSVProcessor`)
- **Health file** (`health.json`) for monitoring

## Frontend & Templating

- **Pure HTML + CSS + Vanilla JS** (responsive navbar, dashboard)
- **Custom string-based template engine** (`Smarty.php`)
  - `{{{var}}}` placeholder replacement
  - `partial()` for looping over collections
- **Layout composition** via header/main/footer concatenation
- **SSE** (Server-Sent Events) stubbed for future real-time updates

## Security & Configuration

- **Vault** class — AES-256-CBC encryption for API keys/tokens
- **Environment variables** via `.env` parsing in `bootstrap.php`
- **Global exception & error handlers** in `bootstrap.php`
- **Custom Logger** service — channel-based files (`app.log`, `worker-csv.log`, etc.)

## DevOps & Infrastructure

- **Docker + Docker Compose** — Nginx + PHP-FPM + MySQL + worker
- **Helper scripts** — `docker.sh`, `setup.sh`, `commit.sh`, `bake.sh`, `unbake.sh`
- **Nginx reverse proxy** (`nginx.conf`)

## Current Capabilities Summary

- Upload CSV → queue job → background processing → update DB
- Responsive dashboard with layout-based views
- Multi-platform video optimization (stubbed posting)
- Secure credential storage (Vault)
- Background daemon with logging & retry
- Migration system via PHP arrays → SQL
- Basic real-time monitoring groundwork (SSE stub, polling stub)

## Skills Not Yet Present (as of March 2026)

- Composer dependencies / packages
- Full OAuth flows (YouTube/TikTok/Instagram)
- Redis / RabbitMQ queue
- Authentication / sessions
- Unit / integration tests
- API layer (REST/GraphQL)
- Caching (Memcached/Redis)
- Feature flags

## Next Likely Skill Additions

- Real social media posting agents
- SSE for live job progress
- Redis-backed queue
- Laravel Queue compatibility layer
- PHPUnit test suite
- GitHub Actions CI/CD