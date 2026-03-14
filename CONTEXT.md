# Sharpishly Debug Context
> Instant state awareness for MVC & Infrastructure alignment.

## 🚀 1. Current Objective
- **Task:** Finalizing MVC refactor and Data Layer migration logic.
- **Status:** Core classes (Db, Registry, HomeModel) aligned. Unit tests implemented for the persistence layer.

## 📂 2. Pathing Context
- **Controller:** `/var/www/html/php/src/Controllers/`
- **Backend Entry:** `/var/www/html/php/index.php` (FrontController)
- **Static Entry:** `/var/www/html/website/index.html`
- **Storage Root:** `/var/www/html/storage/` (Logs, Queue, Uploads)
- **Test Suite:** `/var/www/html/tests/run.php`

## 🛠️ 3. Environment State
- **Docker User:** `0:0 (root)`
- **PHP Version:** `8.2.30`
- **Logs Command:** `docker logs sharpishly-php --tail 20`
- **Registry Check:** `Registry::get(Db::class)` is the source of truth for DB.
- **Auto-Quality:** `tests/run.php` integrated into `dev-up.sh`.

## 📋 4. Recent Logs / Error Output
```text
[OK] Table 'merchandise_inventory' ready
[OK] Table 'orders' ready
[PATCH] Added 'title' column to 'jobs'
[SEED] Added 1 initial job record
Migration completed successfully.

## 🧱 Controller Architecture
- **BaseController:** Owns `db`, `loc`, `json()`, and `render()`.
- **View Pattern:** Layout-based (Header -> Main -> Footer).
- **Variable Injection:** Uses `{$variable}` syntax in `.html` views.

## 🧱 Controller Architecture
- **BaseController:** Owns `db`, `loc`, `json()`, and `render()`.
- **Template Engine:** Smarty is required and integrated into `BaseController`.
- **Pathing:** Uses `dirname(__DIR__, 1)` to reach `/php/views/` from `/php/src/Controllers/`.
- **Rendering:** `renderView()` delegates to `Smarty::render()`.

## 🧱 Controller Architecture
- **HomeController:** Restored `status()` endpoint for AJAX job tracking.
- **BaseController Handshake:** Using parent `render()` and `json()` methods exclusively.