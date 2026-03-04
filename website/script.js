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

class WorkController {
  constructor(container) {
    this.container = container;
  }

  async index() {
    this.container.innerHTML = "<h1>Loading Work Status...</h1>";
    
    try {
      // Fetching your live JSON data
      const response = await fetch('/php/home/csv');
      const jobs = await response.json();

      let tableHtml = `
        <h1>Live Work Status</h1>
        <p>This data is fetched from MySQL via the WorkController.</p>
        <table class="status-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Last Updated</th>
            </tr>
          </thead>
          <tbody>
            ${jobs.map(job => `
              <tr>
                <td>#${job.id}</td>
                <td><span class="badge ${job.status}">${job.status}</span></td>
                <td>${job.processed_rows.toLocaleString()} / ${job.total_rows.toLocaleString()}</td>
                <td>${job.updated_at}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;

      this.container.innerHTML = tableHtml;
    } catch (e) {
      this.container.innerHTML = `<h1>Error</h1><p>Could not connect to the job database.</p>`;
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