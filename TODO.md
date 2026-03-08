# 🚀 Sharpishly Master TODO

## 📂 Phase 1: Infrastructure & MVC Fixes
- [ ] **Log Consolidation:** Redirect all `Logger` output to root `/logs/app.log` and delete `php/app.log`.
- [ ] **CLI Migration:** Replace legacy `bake.sh/unbake.sh` with a native `php console.php` tool.
- [ ] **Smarty Update:** Implement `#each` loop support in `Smarty.php` for job/property listing rendering.
- [ ] **Security:** Implement Token-protection for the `/migrate` route via Vault-stored secrets.

## 🤖 Phase 2: Agentic Ecosystem
- [ ] **Nervous System:** Connect `script.js` to `php/nervous_system.php` via SSE for live "Thought Stream" updates.
- [ ] **Mail Relay:** Configure `msmtp` in Dockerfile to allow `mail_agent.php` to bypass VirtualBox local-only restrictions.
- [ ] **Job Scout API:** Implement Adzuna API interrogation and `jobs` table migration.
- [ ] **Health Checks:** Add a "Heartbeat" monitor to `worker.php` and `mail_agent.php`.

## 🏠 Phase 3: Landlord CRM
- [ ] **Schema:** Create migrations for `properties`, `tenants`, and `payments` tables.
- [ ] **Reminders:** Build the CRON-trigger to move overdue rent alerts into the Mail Queue.
- [ ] **Dashboard:** Create a specific Glassmorphism card for "Portfolio Overview."

## 🖥️ Phase 4: UI/UX (The HUD)
- [ ] **Main Transition:** Replace `index.html` with the new Glassmorphism "HUD" layout.
- [ ] **Vault UI:** Build the dashboard panel for secure API key entry (no more `.env` manual edits).
- [ ] **Async Feedback:** Link the CSS loading spinner to all `requestGet/Post` calls.

## 🧹 Phase 5: Cleanup
- [ ] Delete all remaining `*.sh` scripts from the project root.
- [ ] Refactor `CSVProcessor.php` to use the unified `Logger` service.

# 🛠️ Cyberdeck Project Roadmap

## 🔴 Immediate Phase: Data & UI
- [x] Implement __call proxy in Db.php for native PDO access.
- [x] Build background worker with binary line counting.
- [x] Create SPA Router with Heartbeat polling.
- [ ] **Add CSV Upload form to /csv page with progress indicators.**
- [ ] **Refactor Controllers to use a 'Silent Update' to prevent UI flicker.**

## 🟠 Maintenance & Scaling
- [ ] **Implement Dependency Linting:** Add a script to check for broken method calls across JS/PHP files.
- [ ] **BaseController Inheritance:** Move shared HTML table logic to a parent class.
- [ ] **Scout Agent:** Initial pattern recognition for imported data.

## 🟢 Advanced (Future)
- [ ] Switch Heartbeat polling to Server-Sent Events (SSE) for lower latency.
- [ ] Implement user authentication for the Cyberdeck neural link.

# 🛠️ Cyberdeck Project Roadmap

## 🔴 Immediate Phase: Data & UI
- [x] Implement __call proxy in Db.php for native PDO access.
- [x] Build background worker with binary line counting.
- [x] Create SPA Router with Heartbeat polling.
- [ ] Add CSV Upload form to /csv page with progress indicators.
- [ ] Refactor Controllers to use a 'Silent Update' to prevent UI flicker.

## 🟠 Stability & Quality Gates (New Standard)
- [ ] **Install PHPStan:** Set up static analysis to catch "Undefined Method" errors automatically.
- [ ] **Setup Pre-commit Hooks:** Automate health checks so broken code can't be committed.
- [ ] **Unit Tests:** Create a test suite for the `Db` and `CSVProcessor` classes.

## 🟢 Advanced (Future)
- [ ] Switch Heartbeat polling to Server-Sent Events (SSE).
- [ ] Implement user authentication for the Cyberdeck neural link.

# 🛠️ Cyberdeck Project Roadmap

## 🔴 Immediate Phase: Data & UI
- [x] Implement __call proxy in Db.php.
- [x] Build background worker.
- [x] Create SPA Router with Heartbeat.
- [x] Integrate Composer into Dockerfile.
- [x] Move Landlord Portal to external `view/landlord.htm`.
- [ ] **Refactor:** Remove all remaining inline HTML from `script.js` and migrate to `view/` directory.

## 🟠 Stability & Quality Gates
- [x] Setup GitHub Actions for CI (Linting & PHPStan).
- [ ] **Run First Analysis:** Execute `phpstan` via Docker locally to verify logic.
- [ ] **Contract Mapping:** Document core service methods for manual safety checks.

## 🟢 Advanced (Future)
- [ ] "Scout" Agent: Automated Nike CSV insight extraction.
- [ ] Auto-Form PDF Generation: Create tenancy agreements from the Landlord Portal.

# Sharpishly Project - TODO

## Core Infrastructure
- [x] Create TaskModel, Scheduler, and Action classes
- [x] Refactor Frontend to MVC with BaseModel inheritance
- [x] Install and verify Ollama (llama3.2 + nomic-embed)
- [ ] Remove legacy `website/trigger.php` (Logic now in TaskController)

## AI & RAG Integration
- [ ] Implement `App\Services\OllamaService` to bridge Docker -> Host Ollama
- [ ] Configure `host.docker.internal` in `docker-compose.yml`
- [ ] Build `OllamaRagAction.php` logic to process local documents/CSVs
- [ ] Integrate embedding logic using `nomic-embed-text` for vector search

## Dashboard & UI
- [ ] Wire up `LandlordController` dynamic data fetching from PHP
- [ ] Implement "Real-time" log viewer modal for background worker tasks
- [ ] Connect `BroadcasterController` to the `SocialPostAction` backend

- [ ] DEBUG: Containers are currently entering 'tail -f' fallback mode locally because `vendor/autoload.php` is generated exclusively via GitHub Actions.
- [ ] TASK: Ensure local hardware scanning (`shells/devices.sh`) is performed while containers are in this fallback state to verify USB/PCI pass-through.
- [ ] CI: Verify GitHub Actions successfully generates the production `vendor/` artifact and passes integrity tests.

- [ ] SPA Task: Implement `OllamaGuard`. 
    - Function: Automatically run `ollama ps` before starting a B2B Mug Print.
    - Logic: If an LLM is hogging > 50% RAM, pause the Print or stop the LLM.

    - [ ] SPA Task: Configure `ModelIntelligenceGate`
    - Logic: Default to `llama3.2:1b` for general tasks.
    - Logic: Switch to `phi3.5` only for complex PHP debugging.
    - Constraint: Block `llama3:8b` or larger to prevent Swap-memory death.

- [ ] PERFORMANCE: Enable `OLLAMA_FLASH_ATTENTION=1`.
- [ ] OS: Test `renice` to prioritize the AI process on the AMD A6.
- [ ] PHP: Update `OllamaService.php` to default to `stream: false` for faster internal processing.

- [ ] PERFORMANCE: Swap file-based queue for **Redis** (The lightweight champion).
- [ ] CONFIG: Optimize Nginx `worker_processes` to match the 2-core AMD hardware.
- [ ] ARCHITECTURE: Explicitly avoid RabbitMQ/NATS to preserve RAM for Ollama.