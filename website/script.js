/***********************************************
 * Sharpishly R&D – Application Logic
 ***********************************************/

const app = {
  // ────────────────────────────────────────────────
  // USER
  // ────────────────────────────────────────────────
  user: {
    name: '',
    email: 'guest@sharpishly.com', // Added default email
  },
  localData:{
    local:localStorage
  },
  // ────────────────────────────────────────────────
  // DATA: Navigation, Projects, and Forms
  // ────────────────────────────────────────────────
  menu: [
    { name: "Home",        pageId: "home", active: true },
    // { name: "Login",       pageId: "login-view" },
    { name: "Dashboard",   pageId: "dashboard-view", hidden: true },
    { name: "Quick Start", pageId: "quick-start" },
    // { name: "Features",    pageId: "Features" },
    // { name: "Products",    pageId: "Products", dropdown: [
    //   { name: "Lite", href: "#" }, { name: "Pro", href: "#" }, { name: "Enterprise", href: "#" }
    // ]},
    // { name: "Portal",      dropdown: [
    //   { name: "Customers", href: "#" }, { name: "Clients", href: "#" }, { name: "Staff", href: "#" }
    // ]},
    //{ name: "Pricing",     href: "#" },
    //{ name: "Contact",     href: "#" }
  ],

  projects: [
    { id: 1, title: "Native Mobile Application Lifecycle", status: "Active",    progress: "75%" },
    { id: 2, title: "Neural Network Optimization",         status: "Review",    progress: "40%" },
    { id: 3, title: "Biometric Security API",              status: "Completed", progress: "100%" },
    { id: 4, title: "Quantum Computing Logic",             status: "Active",    progress: "75%" },
  ],

  projectFormFields: [
    { id: "title",  label: "Project Title",  type: "text",  placeholder: "e.g. Quantum Logic", required: true },
    // Current Status removed to reduce user confusion
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

    if (pageId === 'home') this.updateHomeUI();

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
      menuToRender = this.menu.filter(item => item.name === "Home" || item.name === "Dashboard");
      const dash = menuToRender.find(i => i.name === "Dashboard");
      if (dash) dash.hidden = false;
    } else {
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

  container.innerHTML = ''; // clear

  // Header
  const header = document.createElement('div');
  header.style.textAlign = 'left';
  header.style.marginBottom = '2rem';

  const h2 = document.createElement('h2');
  h2.style.fontSize = '2rem';
  h2.textContent = project.title;
  header.appendChild(h2);

  const subtitle = document.createElement('p');
  subtitle.className = 'login-subtitle';
  subtitle.textContent = 'Property Survey Management with Full HR Suite';
  header.appendChild(subtitle);

  // Grid layout
  const grid = document.createElement('div');
  grid.style.display = 'grid';
  grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(320px, 1fr))';
  grid.style.gap = '20px';

  const leftCol = document.createElement('div');
  leftCol.append(
    this.createSurveyBrief(project),
    this.createHRMilestoneTracker()
  );

  const rightCol = document.createElement('div');
  rightCol.append(
    this.createHRAIAssistant(),
    this.createHRLog(),
    this.createHRSupportPanel(),
    this.createLeaveManager(),    
    this.createDocumentVault(),   
    this.createPayrollStatus(),
    this.createGenericComponent()    
  );

  grid.append(leftCol, rightCol);
  container.append(header, grid);
},

createHRMilestoneTracker() {
  const card = document.createElement('div');
  card.className = 'login-card';

  const h3 = document.createElement('h3');
  h3.style.marginBottom = '15px';
  h3.textContent = 'HR Roadmap';
  card.appendChild(h3);

  const timeline = document.createElement('div');
  timeline.style.borderLeft = '2px solid var(--border)';
  timeline.style.paddingLeft = '20px';
  timeline.style.marginLeft = '10px';

  const phases = [
    { status: 'done',    text: 'Phase 1: Onboarding & Training' },
    { status: 'active',  text: 'Phase 2: Performance Management' },
    { status: 'pending', text: 'Phase 3: Compliance & Reporting'   }
  ];

  phases.forEach(phase => {
    const item = document.createElement('div');
    item.style.marginBottom = '20px';
    item.style.position = 'relative';

    const marker = document.createElement('span');
    if (phase.status === 'done') {
      marker.style.color = 'green';
      marker.textContent = '✔';
    } else if (phase.status === 'active') {
      marker.style.color = 'var(--primary)';
      marker.textContent = '●';
    } else {
      marker.style.color = '#ccc';
      marker.textContent = '○';
    }

    const strong = document.createElement('strong');
    strong.textContent = phase.text;

    item.append(marker, document.createTextNode(' '), strong);
    timeline.appendChild(item);
  });

  card.appendChild(timeline);
  return card;
},

createHRAIAssistant() {
  const div = document.createElement('div');
  div.className = 'login-card';
  div.style.border = '1px dashed var(--primary)';

  const h3 = document.createElement('h3');
  h3.style.color = 'var(--primary)';
  h3.textContent = '✨ AI HR Insight';
  div.appendChild(h3);

  const p = document.createElement('p');
  p.style.fontStyle = 'italic';
  p.style.fontSize = '0.9rem';
  p.style.marginTop = '10px';
  p.textContent = 'Optimizing employee performance metrics for survey teams.';
  div.appendChild(p);

  return div;
},

createHRLog() {
  const log = document.createElement('div');
  log.className = 'login-card';
  log.style.backgroundColor = '#1e1e1e';
  log.style.color = '#4ade80';

  const h3 = document.createElement('h3');
  h3.style.color = 'white';
  h3.style.fontFamily = 'monospace';
  h3.textContent = '> HR_SYSTEM_LOG';
  log.appendChild(h3);

  const logContainer = document.createElement('div');
  logContainer.style.fontFamily = 'monospace';
  logContainer.style.fontSize = '0.75rem';
  logContainer.style.height = '100px';
  logContainer.style.overflowY = 'auto';

  const entry = document.createElement('div');
  entry.textContent = `[${new Date().toLocaleTimeString()}] HR database sync active...`;
  logContainer.appendChild(entry);

  log.appendChild(logContainer);
  return log;
},

createSurveyBrief(project) {
  const section = document.createElement('div');
  section.className = 'login-card';

  const h3 = document.createElement('h3');
  h3.textContent = 'Survey Specifications';
  section.appendChild(h3);

  const p1 = document.createElement('p');
  const strong1 = document.createElement('strong');
  strong1.textContent = 'Focus:';
  p1.appendChild(strong1);
  p1.appendChild(document.createTextNode(' Property Evaluation & Reporting'));
  section.appendChild(p1);

  const p2 = document.createElement('p');
  const strong2 = document.createElement('strong');
  strong2.textContent = 'Progress:';
  p2.appendChild(strong2);
  p2.appendChild(document.createTextNode(` ${project.progress}`));
  section.appendChild(p2);

  return section;
},

createHRSupportPanel() {
  const panel = document.createElement('div');
  panel.className = 'login-card';
  panel.style.borderLeft = '5px solid var(--primary)';

  const h3 = document.createElement('h3');
  h3.textContent = 'HR Support';
  panel.appendChild(h3);

  const button = document.createElement('button');
  button.className = 'btn-login';
  button.style.background = 'var(--dark)';
  button.textContent = 'Open HR Ticket';
  panel.appendChild(button);

  return panel;
},

// ────────────────────────────────────────────────
  // HR ADD-ON COMPONENTS (PROGRAMMATIC)
  // ────────────────────────────────────────────────

  createComplianceTracker() {
    const card = document.createElement('div');
    card.className = 'login-card';

    const h3 = document.createElement('h3');
    h3.style.marginBottom = '15px';
    h3.textContent = 'Compliance & Certifications';
    card.appendChild(h3);

    const list = document.createElement('div');

    const certs = [
      { name: 'RICS Certification', status: '✔', color: 'green' },
      { name: 'Asbestos Awareness', status: '●', color: 'orange' },
      { name: 'CSCS Gold Card',    status: '○', color: '#ccc' }
    ];

    certs.forEach(cert => {
      const item = document.createElement('div');
      item.style.marginBottom = '10px';
      
      const icon = document.createElement('span');
      icon.style.color = cert.color;
      icon.style.marginRight = '10px';
      icon.textContent = cert.status;

      const text = document.createElement('span');
      text.style.fontWeight = 'bold';
      text.textContent = cert.name;

      item.append(icon, text);
      list.appendChild(item);
    });

    card.appendChild(list);
    return card;
  },

  createLeaveManager() {
    const div = document.createElement('div');
    div.className = 'login-card';
    div.style.border = '1px dashed var(--primary)';

    const h3 = document.createElement('h3');
    h3.style.color = 'var(--primary)';
    h3.textContent = '📅 Absence Management';
    div.appendChild(h3);

    const p = document.createElement('p');
    p.style.fontSize = '0.9rem';
    p.style.margin = '10px 0';
    p.textContent = 'Remaining Annual Leave: 14 Days';
    div.appendChild(p);

    const btn = document.createElement('button');
    btn.className = 'btn-login';
    btn.style.padding = '5px 15px';
    btn.textContent = 'Request Time Off';
    div.appendChild(btn);

    return div;
  },

  createDocumentVault() {
    const log = document.createElement('div');
    log.className = 'login-card';
    log.style.backgroundColor = '#f8f9fa';
    log.style.color = '#333';

    const h3 = document.createElement('h3');
    h3.textContent = '📂 Digital Personnel Folder';
    log.appendChild(h3);

    const container = document.createElement('div');
    container.style.fontSize = '0.8rem';
    container.style.marginTop = '10px';

    ['employment_contract.pdf', 'id_verification.jpg', 'training_cert_2025.pdf'].forEach(file => {
      const row = document.createElement('div');
      row.style.padding = '8px';
      row.style.borderBottom = '1px solid #ddd';
      row.textContent = `📄 ${file}`;
      container.appendChild(row);
    });

    log.appendChild(container);
    return log;
  },

  createPayrollStatus() {
    const panel = document.createElement('div');
    panel.className = 'login-card';
    panel.style.borderLeft = '5px solid #28a745';

    const h3 = document.createElement('h3');
    h3.textContent = 'Payroll & Expenses';
    panel.appendChild(h3);

    const p = document.createElement('p');
    p.textContent = 'Last Payslip: Jan 2026';
    panel.appendChild(p);

    const btn = document.createElement('button');
    btn.className = 'btn-login';
    btn.style.background = 'var(--dark)';
    btn.textContent = 'Upload Expense Receipt';
    panel.appendChild(btn);

    return panel;
  },

/**
   * Component Template: [Name of Component]
   * Usage: this.[methodName]()
   */
  createGenericComponent() {
    // 1. Create Main Card Container
    const card = document.createElement('div');
    card.className = 'login-card'; // Reuses your existing CSS
    // Optional: add unique styling here
    // card.style.borderTop = '4px solid var(--primary)';

    // 2. Create Header
    const h3 = document.createElement('h3');
    h3.textContent = 'Component Title';
    card.appendChild(h3);

    // 3. Create Content Body
    const body = document.createElement('div');
    body.style.marginTop = '15px';
    
    // Example content: Simple text or data
    const info = document.createElement('p');
    info.style.fontSize = '0.85rem';
    info.textContent = 'Descriptive text or data goes here.';
    body.appendChild(info);

    card.appendChild(body);

    // 4. Create Action Area (Optional)
    const actionBtn = document.createElement('button');
    actionBtn.className = 'btn-login';
    actionBtn.textContent = 'Action Label';
    actionBtn.onclick = () => prettyBug(actionBtn);
    card.appendChild(actionBtn);

    return card;
  },

  // ────────────────────────────────────────────────
  // PROJECT FORMS & DASHBOARD
  // ────────────────────────────────────────────────
  createProjectForm() {
    const form = document.createElement('form');
    this.projectFormFields.forEach(f => {
      const div = document.createElement('div');
      div.className = 'form-group';
      // Apply default guest email if field is email
      const defaultValue = f.id === 'email' ? this.user.email : '';
      div.innerHTML = `<label for="${f.id}">${f.label}</label>
                       <input id="${f.id}" name="${f.id}" type="${f.type}" 
                       placeholder="${f.placeholder}" value="${defaultValue}" required>`;
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
      
      this.projects.push({ 
        id: Date.now(), 
        title: form.title.value.trim(), 
        status: "Active", // Default status applied here now that it's removed from form
        progress: '10%' 
      });

      this.saveToDisk(); // Save to LocalStorage on form submission
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
  // SAVE TO DISK
  // ────────────────────────────────────────────────
  saveToDisk() {
    const sessionData = {
      email: this.user.email || this.getActiveEmail(),
      projects: this.projects
    };
    localStorage.setItem('sharpishly_session', JSON.stringify(sessionData));
    
    console.log("--- LocalStorage Updated ---");
    console.log(localStorage);
  },

  loadFromDisk() {
    const rawData = localStorage.getItem('sharpishly_session');
    if (rawData) {
      const data = JSON.parse(rawData);
      this.localData = data;
      this.projects = data.projects || this.projects;
      if (data.email) {
        this.user.email = data.email;
        document.getElementById('welcome-message').textContent = `Researcher Portal: ${data.email}`;
      }
      return true;
    }
    return false;
  },

  // ────────────────────────────────────────────────
  // INITIALIZATION
  // ────────────────────────────────────────────────
  initAuth() {
    const form = document.getElementById('loginForm');
    if (form) {
      form.addEventListener('submit', e => {
        e.preventDefault();
        const email = form.email.value.trim();
        document.getElementById('welcome-message').textContent = `Researcher Portal: ${email}`;
        this.user.email = email;
        this.saveToDisk();
        this.renderProjects();
        this.refreshNavigation();
        this.showPage('dashboard-view');
      });
    }

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      localStorage.removeItem('sharpishly_session'); // Clear storage on logout
      this.user.email = 'guest@sharpishly.com'; // Reset to default on logout
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
    this.loadFromDisk(); // Hydrate data from disk before rendering
    this.refreshNavigation();
    this.initMobileMenu();
    this.initAuth();
    
    // Auto-route to dashboard if a session exists
    if (this.isAuthenticated()) {
      this.renderProjects();
      this.showPage('dashboard-view');
    } else {
      this.showPage('home');
    }

    //Debug
    //prettyBug(this);
  }
};

window.addEventListener('DOMContentLoaded', () => app.init());