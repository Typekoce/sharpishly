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

/**
 * ROUTER: The Engine
 */
class Router {
  constructor(routes) {
    this.routes = routes;
    this.container = document.getElementById("app");

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
    const path = window.location.pathname;
    const ControllerClass = this.routes[path] || HomeController;
    
    // Create new instance and run the index method
    const controller = new ControllerClass(this.container);
    controller.index();
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
  "/cyberdeck": CyberdeckController
  
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