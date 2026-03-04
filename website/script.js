/**
 * MODEL: The Data
 */
class HomeModel {
  constructor() {
    this.data = {
      title: "Welcome Home",
      description: "This data came from the HomeModel class."
    };
  }
}

/**
 * CONTROLLERS: The Logic
 */
class HomeController {
  constructor(container) {
    this.container = container;
    this.model = new HomeModel(); // Controller initializes its Model
  }

  index() {
    const { title, description } = this.model.data;
    this.container.innerHTML = `
      <h1>${title}</h1>
      <p>${description}</p>
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

  index() {
    this.container.innerHTML = `
      <h1>Work Page</h1>
      <p>This is a simple static route with no Model.</p>
    `;
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