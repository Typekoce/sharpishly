const app = {
  // ────────────────────────────────────────────────
  //  Data
  // ────────────────────────────────────────────────
  menu: [
    { name: "Home",        pageId: "login-view",       active: true },
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
    { id: 1, title: "Quantum Computing Logic",     status: "Active",    progress: "75%" },
    { id: 2, title: "Neural Network Optimization", status: "Review",   progress: "40%" },
    { id: 3, title: "Biometric Security API",      status: "Completed", progress: "100%" }
  ],

  projectFormFields: [
    { id: "title",    label: "Project Title",    type: "text",    placeholder: "e.g. Quantum Logic",     required: true },
    { id: "status",   label: "Current Status",   type: "text",    placeholder: "e.g. Active",            required: true },
    { id: "progress", label: "Progress (%)",     type: "number",  placeholder: "0–100",                  required: true }
  ],

  // ────────────────────────────────────────────────
  //  Routing / Page visibility
  // ────────────────────────────────────────────────
  showPage(pageId) {
    const pageIds = ['login-view', 'dashboard-view', 'quick-start', 'Features', 'Products'];

    pageIds.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.style.display = id === pageId ? 'block' : 'none';
    });

    // One-time form injection on Quick Start
    if (pageId === 'quick-start') {
      const container = document.getElementById('quick-start-form-container');
      if (container && !container.hasChildNodes()) {
        container.appendChild(this.createProjectForm());
      }
    }
  },

  // ────────────────────────────────────────────────
  //  Navigation rendering
  // ────────────────────────────────────────────────
  createNavItems(container, items, isMobile = false) {
    items.forEach(item => {
      const li = document.createElement('li');
      li.className = 'nav-item';
      if (item.dropdown) li.classList.add('dropdown');

      const a = document.createElement('a');
      a.className = item.dropdown ? 'nav-link dropdown-toggle' : 'nav-link';
      a.href = item.href || '#';
      a.textContent = item.name;
      if (item.active) a.classList.add('active');

      a.addEventListener('click', e => {
        e.preventDefault();
        this.handleNavClick(item, isMobile);
      });

      li.appendChild(a);

      if (item.dropdown) {
        const ul = document.createElement('ul');
        ul.className = 'dropdown-menu';
        if (isMobile) {
          Object.assign(ul.style, {
            position: 'static',
            border: 'none',
            boxShadow: 'none',
            margin: '0',
            paddingLeft: '1.2rem'
          });
        }

        item.dropdown.forEach(sub => {
          const sli = document.createElement('li');
          const sa = document.createElement('a');
          sa.className = 'dropdown-item';
          sa.href = sub.href || '#';
          sa.textContent = sub.name;

          sa.addEventListener('click', e => {
            e.preventDefault();
            console.log('Sub-item clicked:', sub.name);
            if (isMobile) document.querySelector('.btn-close')?.click();
          });

          sli.appendChild(sa);
          ul.appendChild(sli);
        });

        li.appendChild(ul);
      }

      container.appendChild(li);
    });
  },

  handleNavClick(item, isMobile) {
    const pageMap = {
      "Home":        () => this.showHome(),
      "Quick Start": () => this.showPage('quick-start'),
      "Features":    () => this.showPage('Features'),
      // add more named routes here when needed
    };

    const handler = pageMap[item.name];
    if (handler) handler();

    if (isMobile) document.querySelector('.btn-close')?.click();
  },

  showHome() {
    const dashboardVisible = document.getElementById('dashboard-view').style.display === 'block';
    const hasWelcomeText = document.getElementById('welcome-message').textContent.trim() !== '';
    this.showPage(dashboardVisible || hasWelcomeText ? 'dashboard-view' : 'login-view');
  },

  // ────────────────────────────────────────────────
  //  Project Form & Card creation
  // ────────────────────────────────────────────────
  createProjectForm() {
    const form = document.createElement('form');
    form.id = 'newProjectForm';

    this.projectFormFields.forEach(field => {
      const group = document.createElement('div');
      group.className = 'form-group';

      const label = document.createElement('label');
      label.htmlFor = field.id;
      label.textContent = field.label;

      const input = document.createElement('input');
      Object.assign(input, {
        id: field.id,
        type: field.type,
        placeholder: field.placeholder,
        required: field.required
      });

      group.append(label, input);
      form.appendChild(group);
    });

    const btn = document.createElement('button');
    btn.type = 'submit';
    btn.className = 'btn-login';
    btn.textContent = 'Add Project';
    form.appendChild(btn);

    form.addEventListener('submit', e => {
      e.preventDefault();
      const data = {
        id: Date.now(),
        title:    form.title.value.trim(),
        status:   form.status.value.trim(),
        progress: form.progress.value + '%'
      };

      this.projects.push(data);
      this.renderDashboard();
      form.reset();
    });

    return form;
  },

  createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card'; // ← rename class if needed

    card.innerHTML = `
      <h3>${project.title}</h3>
      <p class="status">Status: <strong>${project.status}</strong></p>
      <div class="progress-track">
        <div class="progress-fill" style="width: ${project.progress}"></div>
      </div>
      <p class="progress-label">${project.progress}</p>
    `;

    return card;
  },

  renderDashboard() {
    const grid = document.getElementById('project-grid');
    if (!grid) return;

    // Make sure form exists (moved from render to one-time creation)
    if (!document.getElementById('form-wrapper')) {
      const wrapper = document.createElement('div');
      wrapper.id = 'form-wrapper';
      wrapper.className = 'login-card';
      wrapper.innerHTML = '<h3>Register New Research Project</h3>';

      wrapper.appendChild(this.createProjectForm());
      document.querySelector('#dashboard-view .container')?.insertBefore(wrapper, grid);
    }

    grid.innerHTML = '';
    this.projects.forEach(project => {
      grid.appendChild(this.createProjectCard(project));
    });
  },

  // ────────────────────────────────────────────────
  //  Auth simulation
  // ────────────────────────────────────────────────
  async simulateLogin(email) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        if (email.includes('@')) {
          resolve({ email, token: 'fake-jwt-123' });
        } else {
          reject(new Error('Invalid email format'));
        }
      }, 1200);
    });
  },

  initLogin() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = form.querySelector('button');
      const originalText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Authenticating…';

      try {
        const { email } = await this.simulateLogin(form.email.value);
        document.getElementById('welcome-message').textContent = `Researcher Portal: ${email}`;
        this.renderDashboard();
        this.showPage('dashboard-view');
      } catch (err) {
        alert('Login failed: ' + err.message);
      } finally {
        btn.textContent = originalText;
        btn.disabled = false;
      }
    });

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      document.getElementById('loginForm').reset();
      this.showPage('login-view');
    });
  },

  // ────────────────────────────────────────────────
  //  Mobile menu + initialization
  // ────────────────────────────────────────────────
  initMobileMenu() {
    const toggler = document.querySelector('.navbar-toggler');
    const menu    = document.getElementById('mobileMenu');
    const backdrop = document.getElementById('backdrop');
    const closeBtn = document.querySelector('.btn-close');

    if (!toggler || !menu || !backdrop) return;

    const open  = () => { menu.classList.add('show'); backdrop.classList.add('show'); };
    const close = () => { menu.classList.remove('show'); backdrop.classList.remove('show'); };

    toggler.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') close();
    });
  },

  init() {
    // Build both desktop & mobile menus
    const desktopNav = document.querySelector('#navbarNav .navbar-nav');
    const mobileNav  = document.querySelector('#mobileMenu .navbar-nav');

    if (desktopNav) {
      desktopNav.innerHTML = '';
      this.createNavItems(desktopNav, this.menu, false);
    }
    if (mobileNav) {
      mobileNav.innerHTML = '';
      this.createNavItems(mobileNav, this.menu, true);
    }

    this.initMobileMenu();
    this.initLogin();

    // Initial page
    this.showPage('login-view');
  }
};

// ────────────────────────────────────────────────
//  Start
// ────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => app.init());