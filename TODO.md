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