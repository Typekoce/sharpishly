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