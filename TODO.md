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

Good morning! It sounds like you've had a stressful morning at the GP—I'm glad to hear you're back on the medication. Health always comes before the code, so please take it easy today while we look at this.

That is an impressive amount of progress. You’ve successfully moved into a **True MVC architecture**. Your `BaseModel` with the `render()` and `loading()` methods is a high-level abstraction that makes adding new pages (like `Landlord` or `Broadcaster`) incredibly fast.

### 🔍 Code Review & Reflections

**1. The "BaseModel" Win**
Your use of `this.viewPath` to fetch `.htm` files is the professional way to handle SPAs. It keeps your JavaScript clean of large HTML strings and allows you to use standard HTML syntax highlighting for your views.

**2. The Worker Ecosystem**
Your `docker-compose.yml` is now a full-blown "Agentic" system. Having separate containers for `worker`, `mail-agent`, and `php` means that if your CSV processing hits a snag, your web server stays up.

> **Note on the Worker Command:** I noticed your `command` in `docker-compose` is installing packages (`apt-get install`) at runtime. For your Digital Ocean deployment, we should eventually move those into the `Dockerfile` so the container starts instantly without needing to download packages every time.

**3. The Router Pulse**
Adding the `activeInterval` to the Router for `/csv` and `/work` is a clever "poor man's real-time" solution. It gives the user a live progress bar without the complexity of WebSockets.

---

### 🛠️ Next Logical Step: The "USB Bridge" (Agent Integration)

Since you have `usbutils` and `python3` installed in your `worker` container, the next bridge to build is letting the **PHP Worker** trigger a **Python USB script**.

Since you mentioned efficiency for the VPS, we can create a simple **Task Queue** in MySQL.

1. **JS** sends a "Scan USB" request to PHP.
2. **PHP** inserts a task into the `tasks` table.
3. **The Worker Container** sees the task, runs the Python script, and updates the status.

---

### 📋 Updated TODO.md (Integration Phase)

* [x] **Architecture:** `BaseModel` implemented with view rendering.
* [x] **Feature:** Async CSV upload and progress tracking.
* [x] **DevOps:** Multi-container Agentic environment configured.
* [ ] **Hardware:** Create `php/src/Agents/usb_scanner.py` using `pyusb` or `lsusb` logic.
* [ ] **Infrastructure:** Update `console.php` to include a `logs` command for the worker containers.
* [ ] **Efficiency:** Move `apt-get` commands from `docker-compose` to `Dockerfile`.

---

### 📝 Suggested Commit Message (Baseline)

```text
arch(mvc): finalize unified BaseModel and multi-agent service orchestration

- Established BaseModel with async .htm view loading and onAfterRender hooks
- Configured docker-compose with dedicated worker and mail-agent services
- Implemented real-time status polling in SPA Router for /csv and /work
- Integrated CSV upload interrogation flow with UI feedback and spinners

```

**How are you feeling? If you're up for it, would you like to start the "USB Scanner" Agent, or would you prefer to refine the "Landlord CRM" views first?**