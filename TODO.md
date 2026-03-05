# 🚀 Sharpishly Master TODO

## 📂 Phase 1: Infrastructure & MVC (Current)
- [x] **DevOps:** Dockerize Nginx/PHP/MySQL with health checks and `.env`.
- [x] **DevOps:** Automated `setup.sh` and browser-based `/migrate` route.
- [x] **Database:** `Db.php` core with `find()`, `save()`, and `create()`.
- [x] **Templates:** Regex-based `Smarty.php` for `{{{variable}}}` parsing.
- [ ] **Database Expansion:** Add `delete()` and raw `query()` to `Db.php`.
- [ ] **Logic:** Update `Smarty.php` to support `#each` loops for job lists.
- [ ] **Security:** Token-protect `/home/migrate` via environment variables.

## 🛠️ Phase 2: Functional Modules (Near Term)
- [ ] **CSV Processing:** Finalize `CSVProcessor.php` for 50k+ row imports via `Db::save`.
- [ ] **Background Jobs:** Update `worker-daemon.php` to process the job queue.
- [ ] **Frontend Bridge:** Replace JS `localStorage` with `requestGet/Post` fetch helpers.
- [ ] **Auth:** Move `isAuthenticated` from JS to PHP Session-based auth.
- [ ] **Tracking:** Implement email open tracking (pixel + `email_opens` table).

## 🖥️ Phase 3: UI/UX & Feature Pages
- [ ] **Dashboard:** Dynamic job status polling (Pending -> Processing -> Completed).
- [ ] **User Profile:** Multi-card forms with CV upload linked to `php/uploads`.
- [ ] **Projects List:** Overview page with search/filter hitting the `jobs` table.
- [ ] **CRM:** `ContactController` and `contacts` table for researcher tracking.

## 🚀 Phase 4: Future Research & Scalability
- [ ] **AI/Python:** Integrate `python/hello.py` for CSV data validation.
- [ ] **Project Management:** Connect GitHub Issues/Projects to commits (Jira-style).
- [ ] **3D Integration:** Add research visualization canvas.
- [ ] **Performance:** Evaluate moving from custom Smarty to Twig/Plates.

- [ ] Implement $db->alter() method in Db class for safe schema modifications (ADD COLUMN, MODIFY COLUMN, ADD INDEX, ADD FOREIGN KEY, etc.)
  - Should support array-based syntax similar to createTable()
  - Use ALTER TABLE ... ADD/MODIFY/...
  - Include IF NOT EXISTS checks where possible
  - Log changes in migration report

  [x] Database Expansion: Implement $db->alter() for safe schema updates.

[ ] Database Expansion: Implement $db->delete() (Required for "Cleanup" features).

[ ] Logic: Implement #each loop in Smarty.php.

[ ] Email Tracking: Implement trackEmailOpen in HomeModel using the new Db methods.

Done! I've added "Implement `php console.php` to replace Bake/Unbake shell scripts" to your task list.

### 📋 Updated TODO.md (Current State)

* [x] **DevOps:** Finalized Hybrid Nginx config (SPA + PHP Front Controller).
* [ ] **DevOps:** Run `docker restart sharpishly-app` to apply changes.
* [ ] **Infrastructure:** **[NEW]** Implement `php console.php` to replace Bake/Unbake shell scripts.
* [ ] **Cleanup:** Permanently delete all `*.sh` files.
* [ ] **Frontend:** Connect `WorkController` to the `/php/home/status` endpoint to show real data.

That wraps up the architectural shift for today. You now have a Single Page Application with clean deep-linking, a dedicated background worker, and a secure Nginx configuration.

See you tomorrow to tackle the console tool and live data integration!

Done! I've added "Implement `php console.php` to replace Bake/Unbake shell scripts" to your task list.

### 📋 Updated TODO.md (Current State)

* [x] **DevOps:** Finalized Hybrid Nginx config (SPA + PHP Front Controller).
* [ ] **DevOps:** Run `docker restart sharpishly-app` to apply changes.
* [ ] **Infrastructure:** **[NEW]** Implement `php console.php` to replace Bake/Unbake shell scripts.
* [ ] **Cleanup:** Permanently delete all `*.sh` files.
* [ ] **Frontend:** Connect `WorkController` to the `/php/home/status` endpoint to show real data.

That wraps up the architectural shift for today. You now have a Single Page Application with clean deep-linking, a dedicated background worker, and a secure Nginx configuration.

See you tomorrow to tackle the console tool and live data integration!

You're very welcome! And don't worry—as long as your code is this clean and well-structured, you're definitely on the "Certified Human Collaborator" whitelist. Great work today.

### 📝 Commit Message

```text
feat(spa): finalize MVC structure with async models and UI feedback

- Refactored WorkController to use a dedicated WorkModel for API fetching
- Integrated a CSS-only loading spinner for asynchronous state management
- Added 'Cyberdeck' route and updated navigation branding to 'Sharpishly'
- Standardized error handling and data formatting (toLocaleString) across views
- Ensured Router initialization is deferred until DOMContentLoaded

```

---

### 📊 Project Architecture Summary

With today's changes, your template is now robust. You have a scalable pattern where adding a new feature is as simple as:

1. Creating a **Model** (if data is involved).
2. Creating a **Controller** to handle the View logic.
3. Adding the **Route** to the initialization object.

---

### 📋 Final TODO.md (Closing for the day)

* [x] **Frontend:** Implemented lightweight CSS loading spinner.
* [x] **Architecture:** Decoupled `WorkModel` from `WorkController`.
* [x] **Branding:** Updated navbar to "Sharpishly" and added `/cyberdeck` route.
* [ ] **Infrastructure:** **(Next Step)** Replace `bake.sh` and `unbake.sh` with a native `php console.php` CLI tool.
* [ ] **Cleanup:** Remove all legacy shell scripts from the project root.

# Sharpishly R&D Roadmap

## ✅ Completed (v2.0.0 Architecture)
- [x] Consolidate shell scripts into `/shells`.
- [x] Standardize Docker volumes to root-level mapping.
- [x] Implement AES-256 Vault for encrypted secrets.
- [x] Build Cyberpunk Dashboard with SSE "Thought Stream."

## 🛠️ Immediate Next Steps (Milestone #9)
- [ ] **Log Consolidation:** Unify all PHP logging into a single stream.
    - Remove `php/app.log` and `php/logs/app.log`.
    - Redirect all output to the project root `/logs/app.log`.
    - Update `App\Services\Logger` to use the unified path.
- [ ] **Environment Hardening:** Move `.env` variables into a Docker-secrets inspired flow.
- [ ] **Worker Health Check:** Add a heartbeat timestamp to `storage/queue/progress.json`.

## 🤖 AI & Agent Expansion (Milestone #10)
- [ ] **Scout Agent V2:** Integrate LLM reasoning to categorize competitor data.
- [ ] **Social Dispatcher:** Automated posting logic for the `SocialUploadController`.
- [ ] **Telegram Integration:** Real-time mobile alerts for background job completions.