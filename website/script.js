/***********************************************
 * Sharpishly R&D – Application Logic
 ***********************************************/

const app = {
  // ────────────────────────────────────────────────
  // USER
  // ────────────────────────────────────────────────
  user: {
    name: 'Human Resources Example',
    email: 'guest@sharpishly.com', // Added default email
  },
  localData:{
    local:localStorage
  },
  data:{
    page: '',
    employees:{
      1:{id:1,firstname:'steve'},
      2:{id:2,firstname:'Jamie'},
    },
    roles: ['Manager','Customer Service'],
    tax: ['Paye','Self Employed','Fix Term Contract']
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
    //{ id: 2, title: "Neural Network Optimization",         status: "Review",    progress: "40%" },
    //{ id: 3, title: "Biometric Security API",              status: "Completed", progress: "100%" },
    //{ id: 4, title: "Quantum Computing Logic",             status: "Active",    progress: "75%" },
  ],

  projectFormFields: [
    { id: "title",  label: "Project Title",  type: "text",  placeholder: "e.g. Quantum Logic", required: true },
    // Current Status removed to reduce user confusion
    { id: "email",  label: "Email",          type: "email", placeholder: "name@company.com",   required: true }
  ],
  // ────────────────────────────────────────────────
  // SET PAGE
  // ────────────────────────────────────────────────
  setPage(name) {
    this.data.page = name;
  },


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
  // 1. Update the data block first
    this.setPage(pageId);
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
    this.createPayrollStatus(),
    this.createHRSupportPanel(),
    this.createLeaveManager(), 
  );

  const rightCol = document.createElement('div');
  rightCol.append(
    this.showEmployees(),
    this.createSurveyBrief(project),
    this.createHRMilestoneTracker(),  
    this.createDocumentVault(),   
    this.createGenericComponent(),
    this.createHRAIAssistant(),
    this.createHRLog(),    
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
  // SHOW EMPLOYEES (Updated with Search)
  // ────────────────────────────────────────────────
  showEmployees() {
    const card = document.createElement('div');
    card.className = 'login-card';

    const h3 = document.createElement('h3');
    h3.textContent = 'Employees';
    card.appendChild(h3);

    // Create Search Input programmatically
    const searchWrapper = document.createElement('div');
    searchWrapper.style.marginBottom = '15px';
    
    const searchInput = document.createElement('input');
    searchInput.setAttribute('type', 'text');
    searchInput.setAttribute('placeholder', 'Search staff name...');
    searchInput.className = 'form-group'; // Reusing your existing CSS class
    searchInput.style.width = '100%';
    
    // Trigger filter on every keystroke
    searchInput.onkeyup = () => this.filterForEmployee(searchInput.value);
    
    searchWrapper.appendChild(searchInput);
    card.appendChild(searchWrapper);

    const body = document.createElement('div');
    body.id = 'employee-list-container'; // ID for targeting during filter
    
    const info = document.createElement('p');
    info.style.fontSize = '0.85rem';
    info.textContent = 'Please select employee from the list below';
    body.appendChild(info);

    this.getEmployees(body);
    card.appendChild(body);

    return card;
  },

// ────────────────────────────────────────────────
  // FILTER FOR EMPLOYEE (Updated for Error Handling)
  // ────────────────────────────────────────────────
  filterForEmployee(term) {
    const container = document.getElementById('employee-list-container');
    if (!container) return;

    const rows = container.querySelectorAll('div[id^="emp-"]');
    const query = term.toLowerCase();
    let visibleCount = 0;

    rows.forEach(row => {
      const match = row.textContent.toLowerCase().includes(query);
      row.style.display = match ? 'block' : 'none';
      if (match) visibleCount++;
    });

    // If zero matches, show the message
    this.toggleNoResultsMessage(container, visibleCount === 0);
  },

  // ────────────────────────────────────────────────
  // TOGGLE NO RESULTS MESSAGE
  // ────────────────────────────────────────────────
  toggleNoResultsMessage(container, isEmpty) {
    let msgEl = document.getElementById('search-no-results');

    if (!msgEl) {
      msgEl = document.createElement('div');
      msgEl.setAttribute('id', 'search-no-results');
      msgEl.style.padding = '10px';
      msgEl.style.color = 'var(--primary)'; // Using your theme color
      msgEl.style.fontStyle = 'italic';
      msgEl.style.fontSize = '0.8rem';
      msgEl.style.textAlign = 'center';
      msgEl.textContent = 'No matching employees found.';
      container.appendChild(msgEl);
    }

    msgEl.style.display = isEmpty ? 'block' : 'none';
  },
// ────────────────────────────────────────────────
  // GET EMPLOYEES
  // ────────────────────────────────────────────────
  getEmployees(body) {
    const employees = this.data.employees;
    // Store 'this' context for use inside the click handler
    const self = this;

    for (let i in employees) {
      const employee = employees[i];
      const row = document.createElement('div');
      
      row.setAttribute('id', `emp-${employee.id}`);
      row.textContent = employee.firstname; // Safe text assignment
      row.style.border = '1px dashed #ccc';
      row.style.padding = '5px';
      row.style.cursor = 'pointer';

      // Attach click event using the naming convention
// Inside getEmployees loop
row.onclick = function() {
  self.updateWorkspaceHeader(employee.firstname);
  self.mountHRAdminSuite(employee); // The new entry point
};

      body.appendChild(row);
    }
  },
mountHRAdminSuite(employee) {
  const rightCol = document.getElementById('workspace-content').children[1].children[1];
  rightCol.innerHTML = ''; // Clear existing generic cards

  const adminCard = document.createElement('div');
  adminCard.className = 'login-card';
  
  const h3 = document.createElement('h3');
  h3.textContent = `HR Administration: ${employee.firstname}`;
  adminCard.appendChild(h3);

  // Navigation for HR Categories
  const nav = document.createElement('div');
  nav.style.display = 'flex';
  nav.style.gap = '10px';
  nav.style.marginBottom = '15px';

  ['Personal', 'Role', 'Tax', 'Pension'].forEach(cat => {
    const btn = document.createElement('button');
    btn.className = 'btn-login';
    btn.style.padding = '5px 10px';
    btn.style.fontSize = '0.7rem';
    btn.textContent = cat;
    btn.onclick = () => this.renderHRCategory(employee, cat, formArea);
    nav.appendChild(btn);
  });

  const formArea = document.createElement('div');
  formArea.id = 'hr-form-area';
  
  adminCard.append(nav, formArea);
  rightCol.appendChild(adminCard);
  
  // Default to Personal view
  this.renderHRCategory(employee, 'Personal', formArea);
},
renderHRCategory(employee, category, container) {
    container.innerHTML = ''; 
    
    if (category === 'Role') {
      // Use the new Select Field for Roles
      this.createSelectField({id: 'dept', label: 'Department'}, container, ['Operations', 'Surveying', 'HQ']);
      this.createSelectField({id: 'role', label: 'Job Title'}, container, this.data.roles);
    } 
    else if (category === 'Tax') {
      // Use the new Select Field for Tax Status
      this.createSelectField({id: 'tin', label: 'Tax ID'}, container, ['T-800', 'T-1000']); // Example IDs
      this.createSelectField({id: 'code', label: 'Contract Type'}, container, this.data.tax);
    }
    else {
      // Default to standard inputs for Personal/Pension
      const fields = {
        'Personal': [{id: 'dob', label: 'Date of Birth', type: 'date'}],
      };
      const currentFields = fields[category] || [];
      currentFields.forEach(f => this.setFormField(f, container));
    }

    // Add Save Button
    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn-login';
    saveBtn.textContent = `Update ${category} Records`;
    saveBtn.style.marginTop = '15px';
    saveBtn.onclick = () => this.saveHREntry(employee.id, category);
    container.appendChild(saveBtn);
  },
  // ────────────────────────────────────────────────
  // UPDATE WORKSPACE HEADER
  // ────────────────────────────────────────────────
  updateWorkspaceHeader(staffName) {
    const container = document.getElementById('workspace-content');
    if (!container) return;

    // Find or create the assignment badge
    let badge = document.getElementById('assignment-badge');
    
    if (!badge) {
      badge = document.createElement('span');
      badge.id = 'assignment-badge';
      badge.className = 'status'; // Reusing your project-card status style
      badge.style.marginLeft = '15px';
      badge.style.fontSize = '0.9rem';
      
      // Append to the H2 inside the header
      const h2 = container.querySelector('h2');
      if (h2) h2.appendChild(badge);
    }

    badge.textContent = ` (Assigned to: ${staffName})`;
  },
// ────────────────────────────────────────────────
  // SYSTEM ALERTS (Bootstrap Style)
  // ────────────────────────────────────────────────
  alert(message, type = 'success') {
    const container = document.getElementById('alert-container');
    if (!container) return;

    // 1. Create Alert Div
    const alertDiv = document.createElement('div');
    
    // Map types to Bootstrap classes
    // Types: primary, secondary, success, danger, warning, info, light, dark
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
    alertDiv.style.marginBottom = '10px';
    alertDiv.style.display = 'block';

    // 2. Add Message Text
    const textNode = document.createTextNode(message);
    alertDiv.appendChild(textNode);

    // 3. Create Close Button (Standard Bootstrap Close)
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close';
    btn.setAttribute('aria-label', 'Close');
    btn.onclick = () => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    };
    
    alertDiv.appendChild(btn);

    // 4. Add to Stack
    container.appendChild(alertDiv);

    // 5. Lifecycle Management (Auto-remove after 4s)
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
      }
    }, 4000);
  },
  // ────────────────────────────────────────────────
  // PROJECT FORMS & DASHBOARD
  // ────────────────────────────────────────────────
  setFormField(f,form){

    const div = document.createElement('div');
    div.className = 'form-group';

    const label = document.createElement('label');
    label.setAttribute('for',f.id);
    label.innerHTML = f.label;

    msg = 'hello';

    input = document.createElement('input');
    if(f.id === 'title'){
      msg = this.user.name;
    } else if(f.id ==='email'){
      msg = this.user.email;
    }
    input.value = msg;
    input.setAttribute('id',f.id);


    div.appendChild(label);
    div.appendChild(input);
    form.appendChild(div);

    return form;
  },
  createProjectForm() {
    const form = document.createElement('form');
    this.projectFormFields.forEach(f => {
      this.setFormField(f,form);

    });
    const submit = document.createElement('button');
    submit.type = 'submit'; submit.className = 'btn-login'; submit.textContent = 'Add Project';
    form.appendChild(submit);

form.addEventListener('submit', e => {
      e.preventDefault();
      const emailValue = form.email.value.trim();
      const projectTitle = form.title.value.trim();
      
      // 1. Check for duplicates first
      if (this.preventDuplicateProject(projectTitle)) {
        this.alert(`Error: A project named "${projectTitle}" already exists.`, "danger");
        return; // Exit the function to prevent pushing to array
      }

      // 2. Proceed with update if unique
      document.getElementById('welcome-message').textContent = `Researcher Portal: ${emailValue}`;
      this.user.email = emailValue;
      
      this.projects.push({ 
        id: Date.now(), 
        title: projectTitle, 
        status: "Active",
        progress: '10%' 
      });

      this.saveToDisk();
      this.renderProjects();
      this.refreshNavigation();
      
      this.alert(`Project "${projectTitle}" has been created successfully!`, "success");
      this.showPage('dashboard-view');
    });
    return form;
  },
// ────────────────────────────────────────────────
  // PREVENT DUPLICATE PROJECT
  // ────────────────────────────────────────────────
  preventDuplicateProject(title) {
    // Returns true if a project with the same name already exists
    return this.projects.some(p => p.title.toLowerCase() === title.toLowerCase());
  },
  // ────────────────────────────────────────────────
  // PURGE ALL PROJECTS
  // ────────────────────────────────────────────────
  purgeProjects() {
    // 1. Empty the array (preserves the reference)
    this.projects.length = 0;

    // 2. Sync to localStorage so they don't return on refresh
    this.saveToDisk();

    // 3. Refresh the UI
    this.renderProjects();

    // 4. Notify the user
    this.alert("All projects have been removed.", "warning");
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
    // Inside renderProjects() or where you build your header
    const purgeBtn = document.createElement('button');
    purgeBtn.className = 'btn-login';
    purgeBtn.style.background = 'var(--danger-bg)';
    purgeBtn.style.color = 'var(--danger-text)';
    purgeBtn.textContent = 'Purge All Projects';
    purgeBtn.onclick = () => {
        if(confirm("Are you sure? This cannot be undone.")) {
            this.purgeProjects();
        }
};
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
      // Trigger the centralized alert before switching pages
      this.alert("You have been signed out successfully.", "info");
      this.showPage('home');
      this.refreshNavigation();
    });
  },
  hideDashBoardForm(){
    form = document.getElementById('form-wrapper');
    form.style.display = 'none';
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
// ────────────────────────────────────────────────
  // CREATE SELECT FIELD (Dynamic Dropdowns)
  // ────────────────────────────────────────────────
  createSelectField(f, container, optionsArray) {
    const div = document.createElement('div');
    div.className = 'form-group';

    const label = document.createElement('label');
    label.setAttribute('for', f.id);
    label.textContent = f.label;

    const select = document.createElement('select');
    select.setAttribute('id', f.id);
    select.className = 'form-group'; // Reusing your input styling
    select.style.width = '100%';
    select.style.padding = '0.75rem';

    // Build options from the data array
    optionsArray.forEach(opt => {
      const o = document.createElement('option');
      o.value = opt;
      o.textContent = opt;
      select.appendChild(o);
    });

    div.appendChild(label);
    div.appendChild(select);
    container.appendChild(div);
  },
  init() {
    this.loadFromDisk(); // Hydrate data from disk before rendering
    this.refreshNavigation();
    this.initMobileMenu();
    this.initAuth();
    
    // Auto-route to dashboard if a session exists
    if (this.isAuthenticated()) {
      this.alert("Session access granted", "success");
      this.renderProjects();
      this.showPage('dashboard-view');
      this.setPage('dashboard');
      this.hideDashBoardForm();
    } else {
      this.showPage('home');
      this.setPage('home')
    }

    //Debug
    prettyBug(this);
  }
};

window.addEventListener('DOMContentLoaded', () => app.init());