/**
 * 1. UNIFIED BASE MODEL
 */
class BaseModel {
  constructor(container) {
    this.container = typeof container === 'string' ? document.querySelector(container) : container;
    this.viewPath = ''; 
  }
  
  loading() {
    this.container.innerHTML = `
        <div class="loader-container">
          <div class="spinner"></div>
          <p class="loader-text">Fetching live data...</p>
        </div>
      `;
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

class BroadcasterModel extends BaseModel {
  constructor(container) {
    super(container);
    this.viewPath = '/view/broadcaster/broadcaster.htm';
  }
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

class BroadcasterController {
  constructor(container) {
    this.model = new BroadcasterModel(container);
  }
  async index() {
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

class CsvUploadController {
  constructor(container) { this.container = document.querySelector(container); }
  index() {
    this.container.innerHTML = `<h1>Csv Upload</h1><div class="glass-card"><div id="thought-stream">...</div></div>`;
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
  "/csv-upload": CsvUploadController,
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