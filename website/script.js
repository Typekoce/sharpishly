/**
 * 1. UNIFIED BASE MODEL
 */
class BaseModel {
  constructor(container) {
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    this.viewPath = '';
    this.viewPathSubMenu = '';  
  }
  
  loading() {
    this.container.innerHTML = `
        <div class="loader-container">
          <div class="spinner"></div>
          <p class="loader-text">Fetching live data...</p>
        </div>
      `;
  }

activateSubMenu() {
    // Look for elements specifically within the current container or the global HUD
    const toggle = document.querySelector('.sub-menu-toggle');
    const menu = document.getElementById('quick-tools-menu');

    if (!toggle || !menu) return;

    // Reset state
    toggle.setAttribute('aria-expanded', 'false');
    menu.hidden = true;

    // Use a named function so we can clean up if needed
    const handleToggle = (e) => {
      const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', !isExpanded);
      menu.hidden = isExpanded;
    };

    toggle.addEventListener('click', handleToggle);

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !menu.contains(e.target)) {
        toggle.setAttribute('aria-expanded', 'false');
        menu.hidden = true;
      }
    });
  }

  async renderSubmenu() {
      if (!this.viewPathSubMenu) return;

      // Target the specific sub-nav container
      const subContainer = document.querySelector('#sub-nav');
      if (!subContainer) return;

      try {
          const response = await fetch(this.viewPathSubMenu);
          if (!response.ok) throw new Error(`Failed to load: ${response.statusText}`);

          const html = await response.text();
          subContainer.innerHTML = html; // Injects into #sub-nav
          
      } catch (error) {
          console.error("Submenu Error:", error);
      }
  }

  async render() {
    if (!this.viewPath) {
      console.error("View path not defined for", this.constructor.name);
      return;
    }

    this.loading();

    try {
      const response = await fetch(this.viewPath);
      if (!response.ok) throw new Error(`Failed to load view: ${response.statusText}`);

      const html = await response.text();
      this.container.innerHTML = html;
      
      if (typeof this.onAfterRender === 'function') {
        this.onAfterRender();
      }
    } catch (error) {
      this.container.innerHTML = `
        <div class="error-container">
          <p class="error-text">⚠️ Error loading view: ${error.message}</p>
        </div>
      `;
    }
  }
}

/**
 * 2. MODELS
 */
class HomeModel extends BaseModel {
  async getData() {
    try {
      const response = await fetch('/php/home/response');
      return await response.json();
    } catch (error) {
      return { h1: "Offline", description: "Could not connect to the Nervous System." };
    }
  }
}

class WorkModel extends BaseModel {
  async getJobs() {
    const response = await fetch('/php/home/csv');
    if (!response.ok) throw new Error('Network response was not ok');
    return await response.json();
  }
}

class CsvModel extends BaseModel {
  async getJobs() {
    const response = await fetch('/php/csv/status');
    if (!response.ok) throw new Error('Network response was not ok');
    return await response.json();
  }
}

class LandlordModel extends BaseModel {
  constructor(container) {
    super(container);
    this.viewPath = '/view/landlord/landlord.htm'; 
  }

  async getProperties() {
    try {
      const response = await fetch('/php/landlord/properties');
      return await response.json();
    } catch (error) {
      return [];
    }
  }
}

/**
 * Use this model as a template
 */
class BroadcasterModel extends BaseModel {
  constructor(container) {
    super(container);
    this.viewPath = '/view/broadcaster/broadcaster.htm';
    this.viewPathSubMenu = '/view/layout/submenu.htm';
  }
}

class UploadModel extends BaseModel {
  constructor(container) {
    super(container);
    this.viewPath = '/view/csv/upload.htm';
  }

  //
attachCsvUploadListener() {
  const form = document.getElementById('csv-upload-form');
  if (!form) return; // page doesn't have the form

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
alert(e);
    const btn = document.getElementById('upload-btn');
    const statusDiv = document.getElementById('upload-status');
    const statusText = document.getElementById('status-text');
    const resultPre = document.getElementById('upload-result');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Uploading...';
    statusDiv.style.display = 'block';
    statusText.textContent = 'Uploading CSV...';
    resultPre.textContent = '';

    const formData = new FormData(form);

    try {
      const response = await fetch('/php/csv/upload', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      console.log(data);

      if (response.ok && data.success) {
        statusText.textContent = 'Success! CSV uploaded and queued.';
        statusDiv.className = 'alert alert-success';
        resultPre.textContent = data.message || 'Job queued successfully. Processing will start shortly.';
        resultPre.style.color = '#10b981';
      } else {
        throw new Error(data.error || 'Upload failed');
      }
    } catch (error) {
      statusText.textContent = 'Error during upload';
      statusDiv.className = 'alert alert-danger';
      resultPre.textContent = error.message;
      resultPre.style.color = '#dc2626';
    } finally {
      btn.disabled = false;
      btn.innerHTML = 'Submit to Engine';
    }
  });
}
  //
}

/**
 * 3. CONTROLLERS
 */
class HomeController {
  constructor(container) {
    this.model = new HomeModel(container);
  }
  async index() {
    this.model.loading();
    const data = await this.model.getData();
    this.model.container.innerHTML = `<h1>${data.h1}</h1><p>${data.description}</p>`;
  }
}

class CsvController {
  constructor(container) {
    this.model = new CsvModel(container);
  }
  async index() {
    this.model.loading();
    try {
      const jobs = await this.model.getJobs();
      this.model.container.innerHTML = `
        <h1>Csv Interrogation</h1>
        <table class="status-table">
          <thead><tr><th>ID</th><th>Status</th><th>Progress</th><th class="hide-mobile">Updated</th></tr></thead>
          <tbody>
            ${jobs.map(job => {
                const percent = job.total_rows > 0 ? Math.round((job.processed_rows / job.total_rows) * 100) : 0;
                return `<tr>
                    <td>#${job.id}</td>
                    <td><span class="badge ${job.status}">${job.status.toUpperCase()}</span></td>
                    <td><div class="progress-container"><div class="progress-bar" style="width: ${percent}%"></div></div></td>
                    <td class="hide-mobile muted">${job.updated_at}</td>
                </tr>`;
            }).join('')}
          </tbody>
        </table>`;
    } catch (e) { this.model.container.innerHTML = `<h1>Error</h1>`; }
  }
}

class WorkController {
  constructor(container) {
    this.model = new WorkModel(container);
  }
  async index() {
    this.model.loading();
    try {
      const jobs = await this.model.getJobs();
      this.model.container.innerHTML = `<h1>Work Page</h1><table>...</table>`; // (Table logic similar to CSV)
    } catch (e) { this.model.container.innerHTML = `<h1>Error</h1>`; }
  }
}

class LandlordController {
  constructor(container) {
    this.model = new LandlordModel(container);
  }
  async index() {
    await this.model.render();
    this.loadDynamicData();
  }
  loadDynamicData() {
    console.log("Landlord UI dynamic markers initialized.");
  }
}

class UploadController {
  constructor(container) {
    this.model = new UploadModel(container);
  }
  async index() {
    await this.model.render();
    this.model.attachCsvUploadListener();
  }
}

class BroadcasterController {
  constructor(container) {
    this.model = new BroadcasterModel(container);
  }
  async index() {
    await this.model.renderSubmenu();
    await this.model.activateSubMenu();
    await this.model.render();
    this.initBroadcasterLogic();
  }

  initBroadcasterLogic() {
    const broadcastForm = document.getElementById('broadcast-form');
    const socialQueue = document.getElementById('social-queue');
    if (!broadcastForm) return;

    broadcastForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const content = document.getElementById('post-content').value;
        const selectedPlatforms = Array.from(broadcastForm.querySelectorAll('input[name="p"]:checked'))
                                       .map(cb => cb.parentElement.textContent.trim());

        if (selectedPlatforms.length === 0) return alert("Select a platform.");
        
        const queueItem = document.createElement('div');
        queueItem.className = 'queue-item fade-in';
        queueItem.innerHTML = `<strong>${selectedPlatforms.join(' + ')}</strong><p>${content}</p>`;
        socialQueue.prepend(queueItem);
        broadcastForm.reset();
    });
  }
}

class CyberdeckController {
  constructor(container) { this.container = document.querySelector(container); }
  index() {
    this.container.innerHTML = `<h1>Cyberdeck</h1><div class="glass-card"><div id="thought-stream">...</div></div>`;
  }
}

// Simple Static Controllers
class AboutController { constructor(container){ this.c = document.querySelector(container); } index(){ this.c.innerHTML = `<h1>About</h1>`; } }
class ContactController { constructor(container){ this.c = document.querySelector(container); } index(){ this.c.innerHTML = `<h1>Contact</h1>`; } }

/**
 * 4. ROUTER & INITIALIZATION
 */
class Router {
  constructor(routes) {
    this.routes = routes;
    this.container = "#app";
    this.activeInterval = null;
    window.addEventListener("popstate", () => this.loadRoute());
    document.body.addEventListener("click", e => {
      if (e.target.matches("[data-link]")) {
        e.preventDefault();
        history.pushState(null, null, e.target.href);
        this.loadRoute();
      }
    });
    this.loadRoute();
  }

  loadRoute() {
      if (this.activeInterval) clearInterval(this.activeInterval);
      const path = window.location.pathname;
      const ControllerClass = this.routes[path] || HomeController;
      const controller = new ControllerClass(this.container);
      controller.index();
      if (path === '/csv' || path === '/work') {
        this.activeInterval = setInterval(() => controller.index(), 2000);
      }
    }
}

const routes = {
  "/": HomeController,
  "/about": AboutController,
  "/work": WorkController,
  "/contact": ContactController,
  "/cyberdeck": CyberdeckController,
  "/csv": CsvController,
  "/csv-upload": UploadController,
  "/landlord": LandlordController,
  "/broadcaster": BroadcasterController,
};

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Optional: close menu when clicking a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
            });
        });
    }
    new Router(routes);

});