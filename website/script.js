class HomeModel {
  async getData() {
    try {
      // Use the relative path since Nginx handles the routing
      const response = await fetch('/php/home/response');
      return await response.json();
    } catch (error) {
      console.error("Fetch error:", error);
      return { h1: "Error", description: "Could not load data." };
    }
  }
}

class HomeController {
  constructor(container) {
    this.container = container;
    this.model = new HomeModel();
  }

  async index() {
    // Show a quick loading state
    this.container.innerHTML = "<h1>Loading...</h1>";
    
    // Wait for the data
    const data = await this.model.getData();
    
    // Render the real data
    this.container.innerHTML = `
      <h1>${data.h1}</h1>
      <p>${data.description}</p>
    `;
  }
}

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

/**
 * MODEL: The Data
 */
class WorkModel {
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
 * CONTROLLERS: The Logic
 */
class WorkController {
  constructor(container) {
    this.container = container;
    this.model = new WorkModel(); // Controller initializes its Model
  }

  async index() {
    this.container.innerHTML = "<h1>Loading Work Status...</h1>";

    try {
      const jobs = await this.model.getJobs();

      this.container.innerHTML = `
        <h1>Work Page</h1>
        <p>Live status of CSV processing from MySQL.</p>
        <table class="status-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Updated</th>
            </tr>
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
        </table>
      `;
    } catch (error) {
      this.container.innerHTML = `
        <h1>Error</h1>
        <p>Could not load work data. Please ensure the PHP backend is running.</p>
      `;
    }
  }
}

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
  "/contact": ContactController
  
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