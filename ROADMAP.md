# Roadmap – Sharpishly Research & Development

This is a rough, evolving roadmap. Priorities may change based on user feedback and actual needs.

## ✅ Completed (Recent Milestones)
- **Centralized Alert System:** Bootstrap-style programmatic toast notifications.
- **Auth Persistence:** Implemented `localStorage` for sessions and project data.
- **Project Validation:** Duplicate title prevention and logic-based entry.
- **HR Admin Suite:** Dynamic category rendering and data hydration.
- **Dropdown Logic:** Centralized `roles` and `tax` data arrays feeding dynamic selects.

## Near-term (next 1–3 months)
- **Work Assignment Tab:** Link specific employees to project IDs via the HR Suite.
- **Improved Accessibility:** Enhance ARIA attributes and focus management for project forms.
- **UI Refinement:** Add dark mode toggle and smooth scroll behavior.
- **Meta Data:** Add favicon and basic SEO meta tags.
- **Theme Extraction:** Move CSS variables to a separate theme file.

## Medium-term (3–9 months)
- **Status Management:** Edit project status (Active, Review, Completed) directly from the dashboard card.
- **Interactive Workspace:** Build out the "AI R&D Insight" panel to suggest logic based on project titles.
- **Reporting Tool:** Create a "Download Feasibility Report" button to export summaries as PDFs.
- **Hero Components:** Add a modular hero section with background image options for the Home page.
- **Validation:** Implement advanced JS validation for the Quick Start and Dashboard forms.

## Longer-term / nice-to-have
- **Dynamic Logs:** Real-time simulation of system logs within the project workspace.
- **Mock Portals:** Expand the style guide for the "Staff" and "Client" dropdown placeholders.
- **Automated Testing:** Integrate Playwright smoke tests for the navigation filtering logic.
- **Static Generation:** Evaluate lightweight SSGs (like Eleventy) if the site structure grows.

## Not planned (at this time)
- **Framework Migration:** Staying dependency-free (No React, Vue, or Svelte).
- **Live Backend:** Maintaining a client-side focused "mock-live" experience.

## ✅ Completed (Recent Milestones)
- **Containerization:** Successfully dockerized the application using Nginx on an Ubuntu VM.
- **Work Assignment Tab:** Link specific employees to project IDs via the HR Suite.
...


## ✅ Completed
- **Infrastructure:** Dockerized Nginx environment on Ubuntu VM.
- **UI/UX:** Settings page with persistent Dark Mode.
- **Refactor:** Standardized kebab-case naming conventions.

## 🟦 In Progress (Current Milestone)
- **API Connectivity:** Implementing `requestPost` and `requestGet` wrappers for fetch API.
- **Service Layer:** Abstracting data calls to move away from pure LocalStorage.
---
Feedback and pull requests welcome — especially regarding the R&D workspace UX!

**Last updated:** 17th February 2026