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
