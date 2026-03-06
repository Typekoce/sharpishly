// 1. Unified Base Model
class BaseModel {
  constructor(container) {
    this.container = container;
  }
  
  loading() {
    this.container.innerHTML = `
        <div class="loader-container">
          <div class="spinner"></div>
          <p class="loader-text">Fetching live data...</p>
        </div>
      `;
  }
}

/**
 * MODELS
 */
class HomeModel extends BaseModel {
  async getData() {
    try {
      const response = await fetch('/php/home/response');
      return await response.json();
    } catch (error) {
      console.error("Fetch error:", error);
      return { h1: "Offline", description: "Could not connect to the Nervous System." };
    }
  }
}

class WorkModel extends BaseModel { // Added inheritance here
  async getJobs() {
    try {
      const response = await fetch('/php/home/csv');
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error("WorkModel Error:", error);
      throw error;
    }
  }
}

class CsvModel extends BaseModel { // Added inheritance here
  async getJobs() {
    try {
      const response = await fetch('/php/csv/status');
      if (!response.ok) throw new Error('Network response was not ok');
      return await response.json();
    } catch (error) {
      console.error("CsvModel Error:", error);
      throw error;
    }
  }
}

/**
 * CONTROLLERS
 */
class HomeController {
  constructor(container) {
    this.container = container;
    this.model = new HomeModel(container);
  }

  async index() {
    this.model.loading();
    const data = await this.model.getData();
    this.container.innerHTML = `<h1>${data.h1}</h1><p>${data.description}</p>`;
  }
}

class CsvController {
  constructor(container) {
    this.container = container;
    this.model = new CsvModel(container);
  }

  async index() {
    this.model.loading();
    try {
      const jobs = await this.model.getJobs();
this.container.innerHTML = `
        <h1>Csv Interrogation</h1>
        <p>Real-time telemetry from the background worker.</p>
        <table class="status-table">
          <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Progress</th>
                <th class="hide-mobile">Updated</th>
            </tr>
          </thead>
          <tbody>
            ${jobs.map(job => {
                // Calculate percentage safely
                const percent = job.total_rows > 0 
                    ? Math.round((job.processed_rows / job.total_rows) * 100) 
                    : 0;
                
                // Determine if we show a pulse animation
                const pulseClass = job.status === 'processing' ? 'pulse' : '';

                return `
                  <tr data-job-id="${job.id}">
                    <td>#${job.id}</td>
                    <td><span class="badge ${job.status} ${pulseClass}">${job.status.toUpperCase()}</span></td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: ${percent}%"></div>
                            <span class="progress-text">
                                ${job.processed_rows.toLocaleString()} / ${job.total_rows.toLocaleString()} (${percent}%)
                            </span>
                        </div>
                    </td>
                    <td class="hide-mobile muted">${job.updated_at}</td>
                  </tr>
                `;
            }).join('')}
          </tbody>
        </table>`;
    } catch (error) {
      this.container.innerHTML = `<h1>Error</h1><p>Check Dozzle for backend logs.</p>`;
    }
  }
}

class WorkController {
  constructor(container) {
    this.container = container;
    this.model = new WorkModel(container);
  }

  async index() {
    this.model.loading();
    try {
      const jobs = await this.model.getJobs();
      this.container.innerHTML = `
        <h1>Work Page</h1>
        <p>Live status of CSV processing from MySQL.</p>
        <table class="status-table">
          <thead>
            <tr><th>ID</th><th>Status</th><th>Progress</th><th>Updated</th></tr>
          </thead>
          <tbody>
            ${jobs.map(job => `
              <tr>
                <td>${job.id}</td>
                <td><span class="badge ${job.status}">${job.status}</span></td>
                <td>${job.processed_rows.toLocaleString()} / ${job.total_rows.toLocaleString()}</td>
                <td>${job.updated_at}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>`;
    } catch (error) {
      this.container.innerHTML = `<h1>Error</h1><p>Check Dozzle for backend logs.</p>`;
    }
  }
}

class CyberdeckController {
  constructor(container) { this.container = container; }
  index() {
    this.container.innerHTML = `
      <h1>Cyberdeck</h1>
      <div class="glass-card">
        <h3>Agent Thought Stream</h3>
        <div id="thought-stream" class="terminal-body">
          <div class="line muted">Initiating neural link...</div>
        </div>
      </div>`;
    // Tomorrow we will add: new EventSource('/php/nervous_system.php');
  }
}

// About Model & Controller
class AboutController {
  constructor(container) {
    this.container = container;
  }

  index() {
    this.container.innerHTML = `
      <h1>About Page</h1>
      <p>This is a simple static route with no Model.</p>
    `;
  }
}

// Contact Model & Controller
class ContactController {
  constructor(container) {
    this.container = container;
  }

  index() {
    this.container.innerHTML = `
      <h1>Contact Page</h1>
      <p>This is a simple static route with no Model.</p>
    `;
  }
}

// Landlord Model & Controller
class LandlordController {
  constructor(container) {
    this.container = container;
    this.viewPath = './view/landlord.htm'; // Path relative to script.js
  }

  async index() {
    try {
      const response = await fetch(this.viewPath);
      const html = await response.text();

      this.container.innerHTML = html;
      this.loadDynamicData();

    } catch (error) {
      this.container.innerHTML = `<p class="alert">Error loading view: ${error.message}</p>`;
    }
  }

  loadDynamicData() {
    // This is where you'll update your table rows or stats later
    console.log("View loaded. Ready to populate dynamic data.");
  }
}

// Broadcaster Model & Controller
class BroadcasterController {
  constructor(container) {
    this.container = container;
    this.viewPath = './view/broadcaster.htm'; // Path relative to script.js
  }

  populate(){
   // --- Broadcaster Logic ---
    const broadcastForm = document.getElementById('broadcast-form');
    const socialQueue = document.getElementById('social-queue');

    if (broadcastForm) {
        broadcastForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Stop the page from reloading

            // 1. Extract the data
            const content = document.getElementById('post-content').value;
            const selectedPlatforms = Array.from(
                broadcastForm.querySelectorAll('input[name="p"]:checked')
            ).map(cb => cb.parentElement.textContent.trim());

            if (selectedPlatforms.length === 0) {
                alert("Please select at least one platform, Commander.");
                return;
            }

            // 2. Remove the "Empty" message if it exists
            const emptyMsg = socialQueue.querySelector('.empty-msg');
            if (emptyMsg) emptyMsg.remove();

            // 3. Create the Queue Item Component
            const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const queueItem = document.createElement('div');
            queueItem.className = 'queue-item fade-in'; // Added animation class
            
            queueItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong>${selectedPlatforms.join(' + ')}</strong>
                    <span style="font-size: 0.75rem; color: #9ca3af;">Scheduled: ${timestamp}</span>
                </div>
                <p style="font-size: 0.9rem; color: #4b5563;">${content}</p>
                <div style="margin-top: 0.8rem;">
                    <span class="badge success">Pending Sync</span>
                </div>
            `;

            // 4. Inject into the DOM
            socialQueue.prepend(queueItem);

            // 5. Reset the form
            broadcastForm.reset();
        });
    } 
  }// end populate()

  async index() {
    try {
      const response = await fetch(this.viewPath);
      const html = await response.text();

      this.container.innerHTML = html;

      this.loadDynamicData();

      this.populate();

    } catch (error) {
      this.container.innerHTML = `<p class="alert">Error loading view: ${error.message}</p>`;
    }
  }

  loadDynamicData() {
    // This is where you'll update your table rows or stats later
    console.log("View loaded. Ready to populate dynamic data.");
  }
}

/**
 * ROUTER: The Engine
 */
class Router {
  constructor(routes) {
    this.routes = routes;
    this.container = document.getElementById("app");
    this.activeInterval = null; // Track the heartbeat

    // Handle browser Back/Forward buttons
    window.addEventListener("popstate", () => this.loadRoute());

    // Intercept clicks on [data-link] to prevent page refreshes
    document.body.addEventListener("click", e => {
      if (e.target.matches("[data-link]")) {
        e.preventDefault();
        history.pushState(null, null, e.target.href);
        this.loadRoute();
      }
    });

    this.loadRoute(); // Initial load
  }


  loadRoute() {
      if (this.activeInterval) clearInterval(this.activeInterval); // Stop old heartbeats

      const path = window.location.pathname;
      const ControllerClass = this.routes[path] || HomeController;
      
      const controller = new ControllerClass(this.container);
      controller.index();

      // If we are on a data-heavy page, start the Heartbeat
      if (path === '/csv' || path === '/work') {
        this.activeInterval = setInterval(() => controller.index(), 2000);
      }
    }
}

/**
 * INITIALIZATION
 */
const routes = {
  "/": HomeController,
  "/about": AboutController,
  "/work": WorkController,
  "/contact": ContactController,
  "/cyberdeck": CyberdeckController,
  "/csv": CsvController,
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