const app = {
  // ────────────────────────────────────────────────
  // Data
  // ────────────────────────────────────────────────
  menu: [
    { name: "Home",        pageId: "login-view", active: true },
    { name: "Dashboard",   pageId: "dashboard-view", hidden: true },
    { name: "Quick Start", pageId: "quick-start" },
    { name: "Features",    pageId: "Features" },
    { name: "Products",    pageId: "Products", dropdown: [
      { name: "Lite",       href: "#" },
      { name: "Pro",        href: "#" },
      { name: "Enterprise", href: "#" }
    ]},
    { name: "Portal",      dropdown: [
      { name: "Customers", href: "#" },
      { name: "Clients",   href: "#" },
      { name: "Staff",     href: "#" }
    ]},
    { name: "Pricing",     href: "#" },
    { name: "Contact",     href: "#" }
  ],

  projects: [
    { id: 1, title: "Native Mobile Application Lifecycle",     status: "Active",    progress: "75%" },
    { id: 2, title: "Neural Network Optimization", status: "Review",   progress: "40%" },
    { id: 3, title: "Biometric Security API",      status: "Completed", progress: "100%" },
    { id: 4, title: "Quantum Computing Logic",     status: "Active",    progress: "75%" },
  ],

  projectFormFields: [
    { id: "title",    label: "Project Title",    type: "text",    placeholder: "e.g. Quantum Logic", required: true },
    { id: "status",   label: "Current Status",   type: "text",    placeholder: "e.g. Active",        required: true },
    { id: "progress", label: "Progress (%)",     type: "number",  placeholder: "0–100",              required: true }
  ],

  // ────────────────────────────────────────────────
  // Auth state helpers
  // ────────────────────────────────────────────────
  isAuthenticated() {
    return document.getElementById('welcome-message')?.textContent.trim() !== '' ||
           document.getElementById('dashboard-view')?.style.display === 'block';
  },

  // ────────────────────────────────────────────────
  // Page / View switching
  // ────────────────────────────────────────────────
  showPage(pageId) {
    // List includes the new workspace-view
    const pages = ['login-view', 'dashboard-view', 'quick-start', 'Features', 'Products', 'workspace-view'];

    pages.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.style.display = (id === pageId) ? 'block' : 'none';
    });

    if (pageId === 'quick-start') {
      const dashboardItem = this.menu.find(item => item.name === 'Dashboard');
      if (dashboardItem?.hidden) {
        dashboardItem.hidden = false;
        this.refreshNavigation();
      }

      const container = document.getElementById('quick-start-form-container');
      if (container && container.children.length === 0) {
        container.appendChild(this.createProjectForm());
      }
    }
  },

  // ────────────────────────────────────────────────
  // Navigation
  // ────────────────────────────────────────────────
  refreshNavigation() {
    const desktop = document.querySelector('#navbarNav .navbar-nav');
    const mobile  = document.querySelector('#mobileMenu .navbar-nav');

    if (desktop) {
      desktop.innerHTML = '';
      this.buildNavItems(desktop, this.menu, false);
    }
    if (mobile) {
      mobile.innerHTML = '';
      this.buildNavItems(mobile, this.menu, true);
    }
  },

  buildNavItems(container, items, isMobile = false) {
    items.forEach(item => {
      if (item.hidden) return;

      const li = document.createElement('li');
      li.className = 'nav-item';
      if (item.dropdown) li.classList.add('dropdown');

      const link = document.createElement('a');
      link.className = item.dropdown ? 'nav-link dropdown-toggle' : 'nav-link';
      link.href = item.href || '#';
      link.textContent = item.name;
      if (item.active) link.classList.add('active');

      link.addEventListener('click', e => {
        e.preventDefault();
        this.handleNavClick(item, isMobile);
      });

      li.appendChild(link);

      if (item.dropdown) {
        const ul = document.createElement('ul');
        ul.className = 'dropdown-menu';
        if (isMobile) {
          Object.assign(ul.style, {
            position: 'static', border: 'none', boxShadow: 'none',
            margin: '0', paddingLeft: '1.2rem'
          });
        }

        item.dropdown.forEach(sub => {
          const sli = document.createElement('li');
          const a = document.createElement('a');
          a.className = 'dropdown-item';
          a.href = sub.href || '#';
          a.textContent = sub.name;

          a.addEventListener('click', e => {
            e.preventDefault();
            if (isMobile) document.querySelector('.btn-close')?.click();
          });

          sli.appendChild(a);
          ul.appendChild(sli);
        });

        li.appendChild(ul);
      }

      container.appendChild(li);
    });
  },

  handleNavClick(item, isMobile) {
    const actions = {
      "Home":        () => this.showPage(this.isAuthenticated() ? 'dashboard-view' : 'login-view'),
      "Dashboard":   () => this.showPage('dashboard-view'),
      "Quick Start": () => this.showPage('quick-start'),
      "Features":    () => this.showPage('Features'),
    };

    const action = actions[item.name];
    if (action) action();

    if (isMobile) document.querySelector('.btn-close')?.click();
  },
// ────────────────────────────────────────────────
  // Project Workspace (Stakeholder Journey)
  // ────────────────────────────────────────────────

  /**
   * Transitions the user into a specific project workspace.
   * Tailored for stakeholders like the Central Heating Company.
   */
  showWorkspace(project) {
    this.showPage('workspace-view');
    const container = document.getElementById('workspace-content');
    if (!container) return;

    container.innerHTML = '';

    // Header section for the workspace
    const header = document.createElement('div');
    header.style.textAlign = 'left';
    header.style.marginBottom = '2rem';
    header.innerHTML = `
      <h2 style="font-size: 2rem; color: var(--dark);">${project.title}</h2>
      <p class="login-subtitle">R&D Lifecycle: Native Mobile Application Suite</p>
    `;

    // Layout: 2-column grid for Workspace components
    const grid = document.createElement('div');
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(320px, 1fr))';
    grid.style.gap = '20px';

    // Column 1: Technical specs and Roadmap
    const leftCol = document.createElement('div');
    leftCol.append(this.createTechBrief(project), this.createMilestoneTracker());

    // Column 2: AI Insights, Tech Logs, and Support
    const rightCol = document.createElement('div');
    rightCol.append(this.createAIAssistant(), this.createTechLog(), this.createSupportPanel());

    grid.append(leftCol, rightCol);
    container.append(header, grid);
  },

  /**
   * Creates a visual roadmap of project milestones for clients/PMs.
   */
  createMilestoneTracker() {
    const card = document.createElement('div');
    card.className = 'login-card';
    card.style.maxWidth = '100%';
    card.style.marginBottom = '20px';
    
    card.innerHTML = `
      <h3 style="margin-bottom:15px;">R&D Roadmap</h3>
      <div style="border-left: 2px solid var(--border); padding-left: 20px; margin-left: 10px;">
        <div style="margin-bottom: 20px; position: relative;">
          <span style="position: absolute; left: -26px; background: white; color: green;">✔</span>
          <strong>Phase 1: Architecture</strong>
          <p style="font-size: 0.85rem; color: var(--gray);">UI/UX Design & Cloud Mapping (Completed)</p>
        </div>
        <div style="margin-bottom: 20px; position: relative;">
          <span style="position: absolute; left: -26px; background: white; color: var(--primary);">●</span>
          <strong>Phase 2: Native Build</strong>
          <p style="font-size: 0.85rem; color: var(--gray);">iOS Swift & Android Kotlin development (In Progress)</p>
        </div>
        <div style="position: relative;">
          <span style="position: absolute; left: -26px; background: white; color: #ccc;">○</span>
          <strong>Phase 3: Deployment</strong>
          <p style="font-size: 0.85rem; color: var(--gray);">App Store Submission & 24/7 Support Onboarding</p>
        </div>
      </div>
    `;
    return card;
  },

  /**
   * Simulates AI-driven insights for the R&D project.
   */
  createAIAssistant() {
    const div = document.createElement('div');
    div.className = 'login-card';
    div.style.maxWidth = '100%';
    div.style.marginBottom = '20px';
    div.style.border = '1px dashed var(--primary)';
    
    div.innerHTML = `
      <h3 style="color: var(--primary); display: flex; align-items: center; gap: 8px;">
        <span>✨</span> AI R&D Insight
      </h3>
      <p style="font-style: italic; font-size: 0.9rem; margin-top: 10px;">
        "I recommend implementing Biometric Logic for the Central Heating app. This will allow users to securely lock thermostat controls via FaceID/Fingerprint."
      </p>
    `;
    return div;
  },

  /**
   * Creates a scrolling technical log to show cloud/system activity.
   */
  createTechLog() {
    const logContainer = document.createElement('div');
    logContainer.className = 'login-card';
    logContainer.style.maxWidth = '100%';
    logContainer.style.marginBottom = '20px';
    logContainer.style.backgroundColor = '#1e1e1e';
    logContainer.style.color = '#4ade80'; // Neon Green
    
    logContainer.innerHTML = `
      <h3 style="color: white; font-size: 0.9rem; margin-bottom: 10px; font-family: monospace;">> SYSTEM_LOG</h3>
      <div style="font-family: 'Courier New', monospace; font-size: 0.75rem; height: 100px; overflow-y: auto; line-height: 1.4;">
        <div>[${new Date().toLocaleTimeString()}] Initializing AWS environment...</div>
        <div>[${new Date().toLocaleTimeString()}] Swift compiler optimized for iOS 17...</div>
        <div>[${new Date().toLocaleTimeString()}] Android Kotlin Gradle build successful.</div>
        <div>[${new Date().toLocaleTimeString()}] Analyzing sensor data latency...</div>
      </div>
    `;
    return logContainer;
  },

  /**
   * Creates the technical specifications view for the project.
   */
  createTechBrief(project) {
    const section = document.createElement('div');
    section.className = 'login-card';
    section.style.maxWidth = '100%';
    section.style.marginBottom = '20px';

    section.innerHTML = `
      <h3>Technical Specifications</h3>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 1rem;">
        <div>
          <label style="font-size: 0.8rem; color: var(--gray);">Primary Stack</label>
          <p><strong>Native Mobile</strong></p>
        </div>
        <div>
          <label style="font-size: 0.8rem; color: var(--gray);">Progress</label>
          <p><strong>${project.progress}</strong></p>
        </div>
      </div>
    `;
    return section;
  },

  /**
   * Creates the stakeholder support panel for 24/7 lifecycle support.
   */
  createSupportPanel() {
    const panel = document.createElement('div');
    panel.className = 'login-card';
    panel.style.maxWidth = '100%';
    panel.style.borderLeft = '5px solid var(--primary)';

    panel.innerHTML = `
      <h3>24/7 Developer Support</h3>
      <p style="font-size: 0.9rem; margin: 10px 0;">Continuous lifecycle monitoring for the Central Heating Co. mobile suite.</p>
      <button class="btn-login" style="margin-top: 0.5rem; background: var(--dark);">
        Open Support Ticket
      </button>
    `;

    panel.querySelector('button').onclick = () => alert('Support Ticket Initiated. A native developer will be with you shortly.');
    return panel;
  },
  // ────────────────────────────────────────────────
  // Project Form & Cards (Programmatic DOM)
  // ────────────────────────────────────────────────

  /**
   * Dynamically creates the project registration form.
   */
  createProjectForm() {
    const form = document.createElement('form');
    form.id = 'newProjectForm';

    this.projectFormFields.forEach(f => {
      const div = document.createElement('div');
      div.className = 'form-group';
      const label = document.createElement('label');
      label.htmlFor = f.id;
      label.textContent = f.label;
      const input = document.createElement('input');
      Object.assign(input, { id: f.id, name: f.id, type: f.type, placeholder: f.placeholder, required: f.required });
      div.append(label, input);
      form.appendChild(div);
    });

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.className = 'btn-login';
    submit.textContent = 'Add Project';
    form.appendChild(submit);

    form.addEventListener('submit', e => {
      e.preventDefault();
      const values = {
        id: Date.now(),
        title: form.title.value.trim(),
        status: form.status.value.trim(),
        progress: form.progress.value.trim() + '%'
      };
      this.projects.push(values);
      this.renderProjects();
      form.reset();
      this.showPage('dashboard-view');
    });

    return form;
  },

  /**
   * Creates a visual progress bar component.
   */
  createProgressBar(progress) {
    const track = document.createElement('div');
    track.className = 'progress-track';
    const fill = document.createElement('div');
    fill.className = 'progress-fill';
    fill.style.width = progress;
    track.appendChild(fill);
    return track;
  },

  /**
   * Creates a project card with a click event to enter the workspace.
   */
  createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.style.cursor = 'pointer';

    // Journey Continuation: Clicking a card enters the Workspace
    card.onclick = () => this.showWorkspace(project);

    const h3 = document.createElement('h3');
    h3.textContent = project.title;

    const statusP = document.createElement('p');
    statusP.className = 'status';
    statusP.textContent = 'Status: ';
    const strong = document.createElement('strong');
    strong.textContent = project.status;
    statusP.appendChild(strong);

    const progressTrack = this.createProgressBar(project.progress);
    const label = document.createElement('p');
    label.className = 'progress-label';
    label.textContent = project.progress;

    card.append(h3, statusP, progressTrack, label);
    return card;
  },

  /**
   * Renders the project grid in reverse chronological order.
   */
  renderProjects() {
    const grid = document.getElementById('project-grid');
    if (!grid) return;

    if (!document.getElementById('form-wrapper')) {
      const wrapper = document.createElement('div');
      wrapper.id = 'form-wrapper';
      wrapper.className = 'login-card';
      wrapper.innerHTML = '<h3>Register New Research Project</h3>';
      wrapper.appendChild(this.createProjectForm());

      const container = document.querySelector('#dashboard-view .container');
      if (container) container.insertBefore(wrapper, grid);
    }

    grid.innerHTML = '';
    // Reverse order: most recent first
    this.projects.slice().reverse().forEach(p => grid.appendChild(this.createProjectCard(p)));
  },

  // ────────────────────────────────────────────────
  // Auth & Initialization
  // ────────────────────────────────────────────────

  /**
   * Simulates a login delay for the POC.
   */
  simulateLogin(email) {
    return new Promise((resolve, reject) => {
      setTimeout(() => email.includes('@') ? resolve({ email }) : reject(new Error('Invalid email')), 1000);
    });
  },

  /**
   * Initializes authentication listeners and logic.
   */
  initAuth() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      const text = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Authenticating…';

      try {
        const { email } = await this.simulateLogin(form.email.value.trim());
        document.getElementById('welcome-message').textContent = `Researcher Portal: ${email}`;
        this.renderProjects();
        this.showPage('dashboard-view');
        this.refreshNavigation();
      } catch (err) {
        alert('Login failed: ' + err.message);
      } finally {
        btn.textContent = text;
        btn.disabled = false;
      }
    });

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      document.getElementById('loginForm')?.reset();
      this.showPage('login-view');
      this.refreshNavigation();
    });
  },

  /**
   * Initializes mobile menu interactions.
   */
  initMobileMenu() {
    const els = {
      toggler:  document.querySelector('.navbar-toggler'),
      menu:     document.getElementById('mobileMenu'),
      backdrop: document.getElementById('backdrop'),
      close:    document.querySelector('.btn-close')
    };

    if (!els.toggler || !els.menu || !els.backdrop) return;

    const open = () => { els.menu.classList.add('show'); els.backdrop.classList.add('show'); };
    const close = () => { els.menu.classList.remove('show'); els.backdrop.classList.remove('show'); };

    els.toggler.addEventListener('click', open);
    els.close.addEventListener('click', close);
    els.backdrop.addEventListener('click', close);
    document.addEventListener('keydown', e => e.key === 'Escape' && close());
  },

  /**
   * Boots the application and builds the initial UI state.
   */
  init() {
    this.refreshNavigation();
    this.initMobileMenu();
    this.initAuth();
    this.showPage('login-view');
  }
};

window.addEventListener('DOMContentLoaded', () => app.init());