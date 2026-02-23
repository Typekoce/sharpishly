# TODO

```
    Home Page: The landing page that provides an overview of the R&D website, highlights key features, recent projects, and calls-to-action for sign-up or login.

    About Us Page: Details the mission, team, and background of the R&D organization or platform.

    Contact Us Page: Form for inquiries, support requests, and contact information.

    Login Page: Secure authentication page for existing users to access their accounts.

    Sign Up/Registration Page: Form for new users to create accounts, including email verification and basic info collection.

    Forgot Password Page: Allows users to reset their passwords via email or security questions.

    User Profile/Personal Details Page: Editable section for users to manage personal information, such as name, bio, contact details, and preferences.

    Dashboard Page: Personalized hub post-login, showing quick links to projects, notifications, and user-specific data.

    Projects List Page: Overview of all ongoing and past R&D projects, with filters for search and sorting.

    Project Details Page: In-depth view of a specific project, including timelines, milestones, team members, and progress updates.

    Collaboration/Team Page: Tools for team communication, such as forums, chat, or shared workspaces.

    Documents/Repository Page: Upload, share, and manage research papers, datasets, prototypes, and other files.

    CRM Dashboard Page: Central interface for managing contacts, leads, partners, and stakeholder interactions.

    Contacts List Page (within CRM): Directory of stakeholders, clients, or collaborators with search and export options.

    Contact Details Page (within CRM): Detailed profiles for individual contacts, including history and notes.

    Reports/Analytics Page: Generation and viewing of R&D metrics, such as project performance, resource allocation, and KPI dashboards.

    Settings Page: User-specific configurations, like notification preferences, security settings, and account management.

    Admin Panel Page (if applicable): For administrators to manage users, roles, permissions, and site-wide settings.

    Privacy Policy Page: Legal information on data handling and user privacy.

    Terms of Service Page: Outlines usage rules, responsibilities, and agreements for all users.

    FAQ/Help Page: Answers to common questions and guides for using the platform.

    Logout Confirmation Page: Simple page or modal to confirm user logout and redirect to home.
    
```
- [x] Install and configure Docker on Ubuntu VM.
- [x] Create Dockerfile and docker-compose.yml for Nginx.
- [x] Verify host-to-VM networking (Port 8080).
- [ ] Implement Docker container health checks.

- [x] Unify HTML/JS naming conventions (kebab-case).
- [ ] Create `requestGet(url, headers)` helper using Fetch API.
- [ ] Create `requestPost(url, data, headers)` helper.
- [ ] Add global error handling for API timeouts and 404s.
- [ ] Update `submitProject` to use `requestPost` instead of just saving to LocalStorage.

## ✅ Completed
- [x] Create standalone `/docs` manual.
- [x] Implement multi-card User Profile forms.
- [x] Fix card clearance and CSS theme bugs.

## 🟦 Next Up
- [ ] Connect `app.data.roles` to the Expertise dropdown in Profile.
- [ ] Implement `requestGet` / `requestPost` (Milestone #8).
- [ ] Add "Export Profile to PDF" feature.

# TODO

## ✅ MVC & Bug Fixes
- [x] Extract inline HTML to `View` layer.
- [x] Fix `this.projects` scope error.
- [x] Resolve `hideDashBoardForm` null pointer exception.

## 🟦 Next Up (Logic & Data)
- [ ] **Data Hydration:** Ensure `initProfile` saves all fields to `localStorage`.
- [ ] **CRM Foundation:** Define `app.data.contacts` for researcher tracking.
- [ ] **Dynamic Content:** Move `View` strings to `app.data.content` for CMS capability.

# TODO

## ✅ MVC & View Refactor
- [x] Create centralized `View` object for HTML templates.
- [x] Implement `HomeController` and `QuickStartController`.
- [x] Fix `this.projects` undefined error in validation.

## 🟦 Next Up: Component Expansion
- [ ] **SettingsController:** Migrate theme toggling and localData management to MVC.
- [ ] **UserProfileController:** Move personal details and CV upload logic to a controller.
- [ ] **Error Handling:** Add null-checks to `hideDashBoardForm` to prevent init crashes.

## 🟧 Future Modules (The Big Four)
- [ ] **CRM:** Track researcher interactions via `ContactController`.
- [ ] **CMS:** Abstract `View` strings into `app.data.content`.
- [ ] **Social Suite:** Create unified social media upload helpers.
- [ ] **3D Integration:** Add research visualization canvas.

I have updated your local context mentally with the suggested roadmap item. To keep your project documentation in sync, you should add the following entry to the **"Near-term (next 1–3 months)"** section of your `ROADMAP.md`:

* **MVC Migration:** Gradually transition all global `app` functions into dedicated Model and Controller classes (Home, Dashboard, UserProfile, Workspace) to improve maintainability and testability.

---

### What's next on the task list?

Looking at your `TODO.md` and the current state of your `script.js` (where you just added `UserProfileController`), here are the immediate next steps to maintain momentum:

#### 1. Implement `DashboardController.render()` logic

You have the class scaffolded, but it currently only calls `prettyBug(this)`.

* **Task:** Move the project-card generation logic from the old `app.renderProjects()` into this method.
* **Goal:** This will allow the Dashboard to update its view independently whenever a new project is added.

#### 2. Connect `app.data.roles` to the Expertise Dropdown

This is a specific "Next Up" item in your `TODO.md`.

* **Task:** In your new `UserProfileController`, write a method that selects the "Expertise" dropdown in the HTML and populates it using the `app.data.roles` array.
* **Goal:** Ensure that the user profile reflects the centralized data defined in your model.

#### 3. Standardize the `WorkspaceController`

Since you've done Home, Dashboard, and Profile, the **Workspace** is the last major "view" without a dedicated controller.

* **Task:** Create the `WorkspaceController` to handle the employee list and the HR Admin Suite logic.
* **Goal:** Complete the migration of the primary user-facing pages into the MVC pattern.

#### 4. Begin Milestone #8: API Connectivity

Once the Workspace logic is moved, you will be ready to move away from `localStorage`.

* **Task:** Create the `requestGet` and `requestPost` helpers mentioned in your `TODO.md`.
* **Goal:** Transition the `userModel` and `DashboardController` to fetch/save data to a (mock or real) backend instead of just local memory.

**Which of these would you like to tackle first? I can provide the code for the Expertise dropdown population if you want to clear that TODO item.**
