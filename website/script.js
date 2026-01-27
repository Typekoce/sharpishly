/***********************************************
 * Sharpishly R&D – Application Logic
 ***********************************************/

const app = {
  // ────────────────────────────────────────────────
  // USER
  // ────────────────────────────────────────────────
  user:{
    name:'',
    email:'lee@majors.com',
  },
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
      { name: "Lite", href: "#" }, { name: "Pro", href: "#" }, { name: "Enterprise", href: "#" }
    ]},
    { name: "Portal",      dropdown: [
      { name: "Customers", href: "#" }, { name: "Clients", href: "#" }, { name: "Staff", href: "#" }
    ]},
    { name: "Pricing",     href: "#" },
    { name: "Contact",     href: "#" }
  ],

  projects: [
    { id: 1, title: "Native Mobile Application Lifecycle", status: "Active",    progress: "75%" },
    { id: 2, title: "Neural Network Optimization",         status: "Review",    progress: "40%" },
    { id: 3, title: "Biometric Security API",              status: "Completed", progress: "100%" },
    { id: 4, title: "Quantum Computing Logic",             status: "Active",    progress: "75%" },
  ],

  projectFormFields: [
    { id: "title",  label: "Project Title",  type: "text",  placeholder: "e.g. Quantum Logic", required: true },
    { id: "status", label: "Current Status", type: "text",  placeholder: "e.g. Active",        required: true },
    { id: "email",  label: "Email",          type: "email", placeholder: "name@company.com",   required: true }
  ],

  // ────────────────────────────────────────────────
  // AUTH STATE HELPERS
  // ────────────────────────────────────────────────
  isAuthenticated() {
    const msg = document.getElementById('welcome-message')?.textContent.trim();
    return msg !== '' && msg !== null && msg.includes(':');
  },

  getActiveEmail() {
    const msg = document.getElementById('welcome-message')?.textContent;
    return msg ? msg.split(': ')[1]?.trim() : null;
  },

  // ────────────────────────────────────────────────
  // PAGE NAVIGATION
  // ────────────────────────────────────────────────
  showPage(pageId) {
    const pages = ['home', 'login-view', 'dashboard-view', 'quick-start', 'Features', 'Products', 'workspace-view'];

    pages.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.style.display = (id === pageId) ? 'block' : 'none';
    });

    // Rule 1.1: Update Home Page greeting if email is present
    if (pageId === 'home') {
      this.updateHomeUI();
    }

    if (pageId === 'quick-start') {
      const container = document.getElementById('quick-start-form-container');
      if (container && container.children.length === 0) {
        container.appendChild(this.createProjectForm());
      }
    }
  },

  updateHomeUI() {
    const email = this.getActiveEmail();
    const homeH1 = document.querySelector('#home h1');
    const homeSub = document.querySelector('#home p.lead') || document.getElementById('sub-header');
    
    if (email && homeH1) {
      homeH1.textContent = "Welcome Back, Researcher";
      if (homeSub) homeSub.innerHTML = `Session active for: <strong>${email}</strong>`;
    } else if (homeH1) {
      homeH1.textContent = "Sharpishly R&D©";
      if (homeSub) homeSub.textContent = "R&D accessible to everyone";
    }
  },

  refreshNavigation() {
    const desktop = document.querySelector('#navbarNav .navbar-nav');
    const mobile  = document.querySelector('#mobileMenu .navbar-nav');
    const hasEmail = this.isAuthenticated();

    let menuToRender;
    if (hasEmail) {
      // RULE: Only Home and Dashboard if email is present
      menuToRender = this.menu.filter(item => item.name === "Home" || item.name === "Dashboard");
      const dash = menuToRender.find(i => i.name === "Dashboard");
      if (dash) dash.hidden = false;
    } else {
      // RULE: Normal menu minus Dashboard if guest
      menuToRender = this.menu.filter(item => item.name !== "Dashboard");
    }

    if (desktop) { desktop.innerHTML = ''; this.buildNavItems(desktop, menuToRender, false); }
    if (mobile) { mobile.innerHTML = ''; this.buildNavItems(mobile, menuToRender, true); }
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

      link.addEventListener('click', e => {
        e.preventDefault();
        this.handleNavClick(item, isMobile);
      });

      li.appendChild(link);

      if (item.dropdown) {
        const ul = document.createElement('ul');
        ul.className = 'dropdown-menu';
        if (isMobile) Object.assign(ul.style, { position: 'static', border: 'none', boxShadow: 'none', paddingLeft: '1.2rem' });
        
        item.dropdown.forEach(sub => {
          const sli = document.createElement('li');
          const a = document.createElement('a');
          a.className = 'dropdown-item';
          a.textContent = sub.name;
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
  // WORKSPACE & PROJECT COMPONENTS
  // ────────────────────────────────────────────────
  showWorkspace(project) {
    this.showPage('workspace-view');
    const container = document.getElementById('workspace-content');
    if (!container) return;
    container.innerHTML = '';
    // ... (rest of workspace logic remains unchanged)
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
    card.innerHTML = `<h3 style="margin-bottom:15px;">R&D Roadmap</h3>
      <div style="border-left: 2px solid var(--border); padding-left: 20px; margin-left: 10px;">
        <div style="margin-bottom: 20px; position: relative;"><span style="color: green;">✔</span> <strong>Phase 1: Architecture</strong></div>
        <div style="margin-bottom: 20px; position: relative;"><span style="color: var(--primary);">●</span> <strong>Phase 2: Native Build</strong></div>
        <div style="position: relative;"><span style="color: #ccc;">○</span> <strong>Phase 3: Deployment</strong></div>
      </div>`;
    return card;
  },

  createAIAssistant() {
    const div = document.createElement('div');
    div.className = 'login-card';
    div.style.border = '1px dashed var(--primary)';
    div.innerHTML = `<h3 style="color: var(--primary);">✨ AI R&D Insight</h3><p style="font-style: italic; font-size: 0.9rem; margin-top: 10px;">"Optimizing biometric logic for the current build."</p>`;
    return div;
  },

  createTechLog() {
    const log = document.createElement('div');
    log.className = 'login-card';
    log.style.backgroundColor = '#1e1e1e';
    log.style.color = '#4ade80';
    log.innerHTML = `<h3 style="color: white; font-family: monospace;">> SYSTEM_LOG</h3>
      <div style="font-family: monospace; font-size: 0.75rem; height: 100px; overflow-y: auto;">
        <div>[${new Date().toLocaleTimeString()}] UI Navigation updated...</div>
      </div>`;
    return log;
  },

  createTechBrief(project) {
    const section = document.createElement('div');
    section.className = 'login-card';
    section.innerHTML = `<h3>Technical Specifications</h3><p><strong>Stack:</strong> Native Mobile</p><p><strong>Progress:</strong> ${project.progress}</p>`;
    return section;
  },

  createSupportPanel() {
    const panel = document.createElement('div');
    panel.className = 'login-card';
    panel.style.borderLeft = '5px solid var(--primary)';
    panel.innerHTML = `<h3>Support</h3><button class="btn-login" style="background: var(--dark);">Open Ticket</button>`;
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
      const emailValue = form.email.value.trim();
      
      document.getElementById('welcome-message').textContent = `Researcher Portal: ${emailValue}`;

      this.user.email = emailValue;

      prettyBug(this.user);
      
      this.projects.push({ 
        id: Date.now(), 
        title: form.title.value.trim(), 
        status: form.status.value.trim(), 
        progress: '10%' // Default progress value
      });

      this.renderProjects();
      this.refreshNavigation();
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
    if (form) {
      form.addEventListener('submit', e => {
        e.preventDefault();
        document.getElementById('welcome-message').textContent = `Researcher Portal: ${form.email.value.trim()}`;
        this.renderProjects();
        this.refreshNavigation();
        this.showPage('dashboard-view');
      });
    }

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      document.getElementById('welcome-message').textContent = '';
      this.showPage('home');
      this.refreshNavigation();
    });
  },

  initMobileMenu() {
    const toggler = document.querySelector('.navbar-toggler');
    const menu = document.getElementById('mobileMenu');
    const backdrop = document.getElementById('backdrop');
    const close = document.querySelector('.btn-close');
    const hide = () => { menu.classList.remove('show'); backdrop.classList.remove('show'); };
    toggler?.addEventListener('click', () => { menu.classList.add('show'); backdrop.classList.add('show'); });
    [close, backdrop].forEach(el => el?.addEventListener('click', hide));
  },
  init() {
    this.refreshNavigation();
    this.initMobileMenu();
    this.initAuth();
    this.showPage('home');
    //prettyBug(this);
  }
};

window.addEventListener('DOMContentLoaded', () => app.init());