🚀 Sharpishly Master Strategy (Consolidated)
🏗️ Layer 1: Core Engine & Infrastructure
[ ] The "Big Move": Replace all *.sh scripts with php console.php (Bake/Unbake/Status).

[ ] Log Unity: Redirect all Logger output to root /logs/app.log; delete redundant php/app.log.

[ ] Nginx Tuning: Optimize worker_processes for 2-core AMD/VPS; enable Gzip/Brotli.

[ ] Dockerfile Hardening: Move apt-get (usbutils, python, etc.) from docker-compose commands into the Dockerfile for instant container startup.

[ ] PHPStan Integration: Run first static analysis to catch "Undefined Method" errors across the new MVC.

[ ] Vault Security: Implement Token-protection for /migrate and secure API key storage (No manual .env).

🤖 Layer 2: Agentic Ecosystem & AI
[ ] USB Bridge: Create php/src/Agents/usb_scanner.py and link to worker.php task queue.

[ ] Nervous System: Migrate Heartbeat polling to SSE (Server-Sent Events) for live "Thought Stream" updates.

[ ] Ollama Intelligence Gate: * Logic: Use llama3.2:1b for general tasks; phi3.5 for PHP debugging.

OllamaGuard: Auto-check ollama ps; pause heavy tasks if RAM > 50%.

[ ] Mail Relay: Configure msmtp to bypass local restrictions for the mail_agent.php.

[ ] Job Scout: Implement Adzuna API interrogation and pattern recognition for CSV imports.

🖥️ Layer 3: HUD & UI/UX (The Glassmorphism SPA)
[ ] Sub-Menu Functionality: Implement dynamic secondary navigation (Details Pending).

[ ] The "Silent Update": Refactor Controllers to prevent UI flicker during route intervals.

[ ] View Migration: Finish moving all remaining inline HTML from script.js to /view/*.htm.

[ ] HUD Transition: Replace index.html with the final Glassmorphism "HUD" layout.

[ ] Broadcaster Logic: Connect frontend queue to the SocialPostAction backend.

🏠 Layer 4: Landlord CRM & Business Logic
[ ] Schema Migration: Properties, tenants, and payments tables.

[ ] Dynamic Markers: Wire up LandlordController to fetch real DB data.

[ ] Auto-Agreements: Build PDF generation logic for tenancy agreements.

### 🚨 CRITICAL DEADLINE: FRIDAY (March 13, 2026)

**Frontend & UI Consolidation**
* **Menu Refactor:** Reduce main menu to exactly 5 core links; map and implement a unique submenu for every section.
* **Navigation:** Implement breadcrumb components on all views for depth tracking.
* **Core Completion:** Finalize all major layers: Frontend, Backend, Services, API, Messaging, Server, and Client.

**Infrastructure & Reliability**
* **Ollama Dev Workaround:** Implement a "Mock Mode" or background queue to prevent slow VM timeouts during local development.
* **Log Aggregation:** Establish a rock-solid, centralized logging system for all services.
* **Storage Consolidation:** Centralize all persistent assets, including upload folders and system logs, into a unified directory structure.

## 🛠️ Infrastructure & Cleanup
- [ ] **Centralize Logging Architecture**
  - [ ] Delete orphaned log files: `./php/app.log`, `./php/logs/app.log`, and `./python/logs/`.
  - [ ] Audit `Logger.php` to ensure `ROOT_PATH` points strictly to `/var/www/html/storage/logs/`.
  - [ ] Update `ai-worker.php` and `scheduler.php` to use the centralized Logger service rather than local `file_put_contents`.
  - [ ] Add `*.log` to the root `.gitignore` to prevent stray local logs from being committed.