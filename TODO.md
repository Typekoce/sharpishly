# 🚀 Sharpishly Master Strategy: Phase 2
> **Current Focus:** Professionalization, AI Integration, and UI "Glassmorphism" HUD.

### 🧱 Layer 1: Core Engine & Infrastructure
- [ ] **Code Sanitization:** Remove all `die()` and `exit()` commands; replace with `throw new Exception()` or graceful `BaseController::json()` responses.
- [ ] **The "Big Move":** Replace all `*.sh` scripts with `php console.php` (Bake/Unbake/Status).
- [ ] **Log Unity:** - [ ] Redirect all output to `/storage/logs/app.log`.
    - [ ] Audit `Logger.php` for `ROOT_PATH` alignment.
    - [ ] Delete orphaned logs: `./php/app.log`, `./php/logs/`, and `./python/logs/`.
- [ ] **Dockerfile Hardening:** Move `apt-get` (usbutils, etc.) from `docker-compose` into the Dockerfile for instant startup.
- [ ] **Nginx Tuning:** Optimize `worker_processes` for 2-core VPS; enable Gzip/Brotli.
- [ ] **Static Analysis:** Integrate **PHPStan** to catch "Undefined Method" errors across the new MVC.
- [ ] **Vault Security:** Implement Token-protection for `/migrate` and secure API key storage.

### 🤖 Layer 2: Agentic Ecosystem & AI
- [ ] **USB Bridge:** Create `php/src/Agents/usb_scanner.py` and link to `worker.php` task queue.
- [ ] **Nervous System:** Migrate Heartbeat polling to **SSE (Server-Sent Events)** for live "Thought Stream" updates.
- [ ] **Ollama Intelligence Gate:**
    - [ ] Logic: `llama3.2:1b` (General) / `phi3.5` (PHP Debugging).
    - [ ] **OllamaGuard:** Auto-pause heavy tasks if RAM > 50%.
- [ ] **Mail Relay:** Configure `msmtp` for `mail_agent.php`.
- [ ] **Job Scout:** Implement Adzuna API interrogation for CSV pattern recognition.

### 🖥️ Layer 3: HUD & UI/UX (Glassmorphism SPA)
- [ ] **HUD Transition:** Replace `index.html` with the finalized Glassmorphism "HUD" layout.
- [ ] **Menu Refactor:** - [ ] Limit main menu to 5 core links.
    - [ ] Implement dynamic secondary sub-navigation for every section.
- [ ] **View Migration:** Move all remaining inline HTML from `script.js` to `/php/views/*.tpl`.
- [ ] **The "Silent Update":** Refactor Controllers to prevent UI flicker during route intervals.
- [ ] **Navigation:** Implement breadcrumb components for depth tracking.

### 🏠 Layer 4: Landlord CRM & Business Logic
- [ ] **Schema Expansion:** Properties, tenants, and payments tables.
- [ ] **Dynamic Markers:** Wire `LandlordController` to fetch real-time DB data.
- [ ] **Auto-Agreements:** Build PDF generation logic for tenancy agreements.

---

## 🛠️ Infrastructure & Cleanup (Current Sprint)
- [x] **Namespace Synchronization:** (Registry, Db, Location aligned).
- [ ] **Centralize Logging Architecture**
  - [ ] Update `ai-worker.php` and `scheduler.php` to use Centralized Logger.
  - [ ] Add `*.log` to root `.gitignore`.
- [ ] **Ollama Dev Workaround:** Implement "Mock Mode" to prevent VM timeouts during local development.
- [ ] **Storage Consolidation:** Centralize all persistent assets (uploads/logs) into `/storage/`.

---

### 🚨 CRITICAL DEADLINE: FRIDAY (March 13, 2026)
* **Menu Refactor:** Exactly 5 core links; map and implement submenus.
* **Storage:** Unified directory structure for assets and logs.# 🚀 Sharpishly Master Strategy: Phase 2

### 🏗️ Layer 1: Core Engine & Infrastructure
- [ ] **Docker Fix:** Resolve `unmarshal errors` on line 2 of `docker-compose.yml`.
- [ ] **Code Sanitization:** Remove all remaining `die()` and `exit()` commands.
- [ ] **Migration Reliability:** Fix `Duplicate column` error in `HomeModel::migrate()`.

### 🖥️ Layer 3: HUD & UI/UX
- [ ] **script.js Refactor:** - [ ] Implement `AppState` object for job tracking.
  - [ ] Replace string-concatenation HTML with Template Literals.
  - [ ] Connect `upload()` response to the HUD "Toast" system.
- [ ] **Csv View:** Finalize `php/views/csv/upload.tpl` Smarty layout.

# 🚀 Sharpishly Master Strategy: Phase 2

### 🏗️ Layer 1: Core Engine & Infrastructure
- [x] **Namespace Synchronization:** All core services aligned under `App\`.
- [x] **View Refactor:** Moved `views/` out of `src/` to comply with PSR-4.
- [ ] **Docker Fix:** Resolve `unmarshal errors` on line 2 of `docker-compose.yml`.
- [ ] **Sanitization:** Final sweep to replace `die()`/`exit()` with Exceptions.

### 🖥️ Layer 3: HUD & UI/UX (Glassmorphism)
- [ ] **script.js Refactor:** - [ ] Implement `AppState` to track `csv_records` progress.
  - [ ] Connect `CsvController::status()` to the HUD progress bars.
- [ ] **View Verification:** Audit `BaseController` to ensure it looks for views in `php/views/` (one level up from `src`).

# 🚀 Sharpishly Master Strategy: Phase 2

### 🧱 Layer 1: Core Engine & Infrastructure
- [ ] **Docker Fix:** Resolve `unmarshal errors` on line 2 of `docker-compose.yml`.
- [ ] **CsvProcessor Logic:** - [ ] Fix double-root pathing in `process()`.
    - [ ] Resolve `file_path` SQL constraint during `updateJobStatus()`.
- [ ] **Sanitization:** Final sweep to replace `die()`/`exit()` with Exceptions.

### 🖥️ Layer 3: HUD & UI/UX (Glassmorphism)
- [ ] **script.js Refactor:** Connect UI to `CsvController::status()` JSON.
- [ ] **View Verification:** Ensure all `.html` templates are recognized in `php/views/`.

### 🏗️ Layer 1: Core Engine & Testability
- [ ] **CrmController Audit:** - [ ] Fix `.` to `->` syntax.
    - [ ] Replace `exit` with return/response objects.
    - [ ] Align Logger namespace with `App\Services\Logger`.
- [ ] **TenantModel:** Verify `getAllTenants()` is using the `Db` service and not raw PDO.

### 🤖 Layer 4: Autonomous Agency (Mocks)
- [ ] **Crm Summarizer:** Mock a "Claude Agent" to provide a 1-sentence summary for each tenant.