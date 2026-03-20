# 🚀 Sharpishly System Context
> Status: MVC Refactor & Infrastructure Alignment Stabilized.

## 📂 1. Core Architecture & Pathing
- **Namespace:** `App\` (PSR-4 compliant via custom bootstrap).
- **Registry:** `Registry::get(Db::class)` is the singleton source of truth.
- **Entry Points:**
  - Backend: `/php/index.php` (FrontController)
  - Frontend: `/website/index.html` (Static SPA entry)
  - Service: `/php/src/bootstrap.php` (Auto-pathing & Registry init)
- **Storage:** `/storage/` (Permissions: 777). Sub-dirs: `uploads/`, `queue/`, `logs/`.

## 🛠️ 2. Data Layer Protocol
- **Pattern:** Structured Array-Driven (Conditions Pattern).
- **Migration Logic:** Idempotent `createTable(string, array)` and `columnExists()` checks.
- **Table Standard:** All workflow tables (`jobs`, `users`, `social`) share `$standardFields` (status, processed_rows, total_rows).
- **Recent Patch:** Added `note` column to `jobs` table via `HomeModel::migrate()`.

## 🧱 3. MVC & View Engine
- **BaseController:** - Properties: `db`, `loc`, `smarty`.
  - Methods: `render()`, `json()`.
- **Template Engine:** Smarty 4.x (Integrated into `BaseController`).
- **View Resolution:** `dirname(__DIR__, 1)` from Controllers to `/php/views/`.
- **View Pattern:** Layout-based: `header.tpl` -> `main.tpl` -> `footer.tpl`.

## 🧪 4. Quality Gate (tests/run.php)
- **Services:** ✅ PASS (Location path resolution verified).
- **Database:** ✅ PASS (Table creation & CRUD verified).
- **MVC Core:** ⚠️ Current 'note' column mismatch in `test_table` (Logic stable, data state loop).
- **Environment:** PHP 8.2.30 / Docker (root).

## 📋 5. Immediate Next Steps
1. **YAML Fix:** Resolve `unmarshal errors` in `docker-compose.yml` line 2.
2. **Test Cleanup:** Clear `test_table` in `DbTest.php` to break the 'Unknown column' loop.
3. **Dashboard:** Verify `HomeController::status()` AJAX endpoint for real-time progress.

# 🏛️ Sharpishly Architectural Context

### 🧱 Database Layer
- **No Raw SQL:** Direct execution of raw SQL strings is strictly prohibited in Controllers, Models, and Services.
- **Abstraction Requirement:** All database operations must use the `App\Db` service.
- **Testability:** By using the abstraction layer, we ensure that all database interactions can be mocked during unit testing.

### 🤖 AI & Agents
- Agents must interface with the `Db` service via Models to ensure data integrity and audit logging.