# Commit Template

<type>(<scope>): <short summary>  (50 chars or less)

<optional longer description>

- Bullet points of what changed / why (if needed)
- Another specific change or decision
- Links to issues/PRs if relevant (e.g. Closes #12)

BREAKING CHANGE: (only if this commit introduces a breaking change)

Co-authored-by: Name <email> (optional)

Example 1

feat: initial project structure with navbar, offcanvas and landing page

- Set up basic HTML skeleton with navbar
- Added vanilla JS menu builder (app.menu + dynamic rendering)
- Implemented mobile offcanvas menu with backdrop
- Created lightweight Bootstrap-inspired CSS
- Added CONTRIBUTORS.md, CHANGELOG.md and ROADMAP.md


Example 2 (small fix)

fix(navbar): prevent mobile menu from staying open on Esc outside modal

- Added document-level keydown listener for Escape
- Ensured backdrop click also closes menu

Example 3 Refactor

refactor: extract mobile menu toggle logic into separate function

- Moved open/close logic to dedicated functions
- Improved readability and reusability

Example 4 – Docs update

docs(readme): fix project structure code block rendering

- Corrected markdown syntax for directory tree
- Added Quick start and Customization sections