/***********************************************
 * Sharpishly R&D – Application Logic
 ***********************************************/

const app = {
  // ────────────────────────────────────────────────
  // DATA: Navigation, Projects, and Forms
  // ────────────────────────────────────────────────
  menu: [
    { name: "Home",        pageId: "home", active: true },
    { name: "Login",       pageId: "login-view" },
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
  // AUTH STATE HELPERS
  // ────────────────────────────────────────────────
  isAuthenticated() {
    return document.getElementById('welcome-message')?.textContent.trim() !== '' ||
           document.getElementById('dashboard-view')?.style.display === 'block';
  },

  // ────────────────────────────────────────────────
  // PAGE NAVIGATION: Controls visibility of all sections
  // ────────────────────────────────────────────────
  showPage(pageId) {
    const pages = ['home', 'login-view', 'dashboard-view', 'quick-start', 'Features', 'Products', 'workspace-view'];

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

  refreshNavigation() {
    const desktop = document.querySelector('#navbarNav .navbar-nav');
    const mobile  = document.querySelector('#mobileMenu .navbar-nav');
    if (desktop) { desktop.innerHTML = ''; this.buildNavItems(desktop, this.menu, false); }
    if (mobile) { mobile.innerHTML = ''; this.buildNavItems(mobile, this.menu, true); }
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
          Object.assign(ul.style, { position: 'static', border: 'none', boxShadow: 'none', margin: '0', paddingLeft: '1.2rem' });
        }
        item.dropdown.forEach(sub => {
          const sli = document.createElement('li');
          const a = document.createElement('a');
          a.className = 'dropdown-item';
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
      "Home":        () => this.showPage('home'),
      "Login":       () => this.showPage('login-view'),
      "Dashboard":   () => this.showPage('dashboard-view'),
      "Quick Start": () => this.showPage('quick-start'),
      "Features":    () => this.showPage('Features'),
    };
    const action = actions[item.name];
    if (action) action();
    if (isMobile) document.querySelector('.btn-close')?.click();
  },

  // ────────────────────────────────────────────────
  // WORKSPACE COMPONENTS: Stakeholder Dashboard
  // ────────────────────────────────────────────────
  showWorkspace(project) {
    this.showPage('workspace-view');
    const container = document.getElementById('workspace-content');
    if (!container) return;
    container.innerHTML = '';

    const header = document.createElement('div');
    header.style.textAlign = 'left';
    header.style.marginBottom = '2rem';
    header.innerHTML = `<h2 style="font-size: 2rem;">${project.title}</h2><p class="login-subtitle">R&D Lifecycle: Native Mobile Application Suite</p>`;

    const grid = document.createElement('div');
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(320px, 1fr))';
    grid.style.gap = '20px';

    const leftCol = document.createElement('div');
    leftCol.append(this.createTechBrief(project), this.createMilestoneTracker());

    const rightCol = document.createElement('div');
    rightCol.append(this.createAIAssistant(), this.createTechLog(), this.createSupportPanel());

    grid.append(leftCol, rightCol);
    container.append(header, grid);
  },

  createMilestoneTracker() {
    const card = document.createElement('div');
    card.className = 'login-card';
    card.style.maxWidth = '100%';
    card.style.marginBottom = '20px';
    card.innerHTML = `
      <h3 style="margin-bottom:15px;">R&D Roadmap</h3>
      <div style="border-left: 2px solid var(--border); padding-left: 20px; margin-left: 10px;">
        <div style="margin-bottom: 20px; position: relative;"><span style="position: absolute; left: -26px; background: white; color: green;">✔</span><strong>Phase 1: Architecture</strong><p style="font-size: 0.85rem; color: var(--gray);">UI/UX Design & Cloud Mapping (Completed)</p></div>
        <div style="margin-bottom: 20px; position: relative;"><span style="position: absolute; left: -26px; background: white; color: var(--primary);">●</span><strong>Phase 2: Native Build</strong><p style="font-size: 0.85rem; color: var(--gray);">iOS Swift & Android Kotlin development (In Progress)</p></div>
        <div style="position: relative;"><span style="position: absolute; left: -26px; background: white; color: #ccc;">○</span><strong>Phase 3: Deployment</strong><p style="font-size: 0.85rem; color: var(--gray);">App Store Submission & 24/7 Support Onboarding</p></div>
      </div>`;
    return card;
  },

  createAIAssistant() {
    const div = document.createElement('div');
    div.className = 'login-card';
    div.style.maxWidth = '100%';
    div.style.marginBottom = '20px';
    div.style.border = '1px dashed var(--primary)';
    div.innerHTML = `<h3 style="color: var(--primary);">✨ AI R&D Insight</h3><p style="font-style: italic; font-size: 0.9rem; margin-top: 10px;">"I recommend implementing Biometric Logic for the Central Heating app for secure thermostat locking."</p>`;
    return div;
  },

  createTechLog() {
    const log = document.createElement('div');
    log.className = 'login-card';
    log.style.backgroundColor = '#1e1e1e';
    log.style.color = '#4ade80';
    log.innerHTML = `<h3 style="color: white; font-size: 0.9rem; margin-bottom: 10px; font-family: monospace;">> SYSTEM_LOG</h3>
      <div style="font-family: monospace; font-size: 0.75rem; height: 100px; overflow-y: auto;">
        <div>[${new Date().toLocaleTimeString()}] AWS Instance active...</div>
        <div>[${new Date().toLocaleTimeString()}] Swift compiler optimized...</div>
        <div>[${new Date().toLocaleTimeString()}] Gradle build successful.</div>
      </div>`;
    return log;
  },

  createTechBrief(project) {
    const section = document.createElement('div');
    section.className = 'login-card';
    section.style.maxWidth = '100%';
    section.style.marginBottom = '20px';
    section.innerHTML = `<h3>Technical Specifications</h3><div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 1rem;">
      <div><label style="font-size: 0.8rem; color: var(--gray);">Primary Stack</label><p><strong>Native Mobile</strong></p></div>
      <div><label style="font-size: 0.8rem; color: var(--gray);">Progress</label><p><strong>${project.progress}</strong></p></div></div>`;
    return section;
  },

  createSupportPanel() {
    const panel = document.createElement('div');
    panel.className = 'login-card';
    panel.style.borderLeft = '5px solid var(--primary)';
    panel.innerHTML = `<h3>24/7 Developer Support</h3><p style="font-size: 0.9rem;">Continuous lifecycle monitoring active.</p><button class="btn-login" style="margin-top: 0.5rem; background: var(--dark);">Open Ticket</button>`;
    panel.querySelector('button').onclick = () => alert('Support Ticket Initiated.');
    return panel;
  },

  // ────────────────────────────────────────────────
  // PROJECT FORMS & DASHBOARD
  // ────────────────────────────────────────────────
  createProjectForm() {
    const form = document.createElement('form');
    this.projectFormFields.forEach(f => {
      const div = document.createElement('div');
      div.className = 'form-group';
      div.innerHTML = `<label for="${f.id}">${f.label}</label><input id="${f.id}" name="${f.id}" type="${f.type}" placeholder="${f.placeholder}" required>`;
      form.appendChild(div);
    });
    const submit = document.createElement('button');
    submit.type = 'submit'; submit.className = 'btn-login'; submit.textContent = 'Add Project';
    form.appendChild(submit);
    form.addEventListener('submit', e => {
      e.preventDefault();
      this.projects.push({ id: Date.now(), title: form.title.value.trim(), status: form.status.value.trim(), progress: form.progress.value.trim() + '%' });
      this.renderProjects();
      this.showPage('dashboard-view');
    });
    return form;
  },

  createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.style.cursor = 'pointer';
    card.onclick = () => this.showWorkspace(project);
    card.innerHTML = `<h3>${project.title}</h3><p class="status">Status: <strong>${project.status}</strong></p>
      <div class="progress-track"><div class="progress-fill" style="width: ${project.progress}"></div></div>
      <p class="progress-label">${project.progress}</p>`;
    return card;
  },

  renderProjects() {
    const grid = document.getElementById('project-grid');
    if (!grid) return;
    if (!document.getElementById('form-wrapper')) {
      const wrapper = document.createElement('div');
      wrapper.id = 'form-wrapper'; wrapper.className = 'login-card';
      wrapper.innerHTML = '<h3>Register New Research Project</h3>';
      wrapper.appendChild(this.createProjectForm());
      document.querySelector('#dashboard-view .container').insertBefore(wrapper, grid);
    }
    grid.innerHTML = '';
    this.projects.slice().reverse().forEach(p => grid.appendChild(this.createProjectCard(p)));
  },

  // ────────────────────────────────────────────────
  // INITIALIZATION
  // ────────────────────────────────────────────────
  initAuth() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', async e => {
      e.preventDefault();
      document.getElementById('welcome-message').textContent = `Researcher Portal: ${form.email.value.trim()}`;
      this.renderProjects();
      this.showPage('dashboard-view');
      this.refreshNavigation();
    });
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      this.showPage('login-view');
      this.refreshNavigation();
    });
  },

  initMobileMenu() {
    const toggler = document.querySelector('.navbar-toggler');
    const menu = document.getElementById('mobileMenu');
    const backdrop = document.getElementById('backdrop');
    const close = document.querySelector('.btn-close');
    const open = () => { menu.classList.add('show'); backdrop.classList.add('show'); };
    const hide = () => { menu.classList.remove('show'); backdrop.classList.remove('show'); };
    toggler?.addEventListener('click', open);
    close?.addEventListener('click', hide);
    backdrop?.addEventListener('click', hide);
  },

  init() {
    this.refreshNavigation();
    this.initMobileMenu();
    this.initAuth();
    this.showPage('home'); // Boots to Home content
  }
};

window.addEventListener('DOMContentLoaded', () => app.init());