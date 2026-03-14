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
* **Storage:** Unified directory structure for assets and logs.