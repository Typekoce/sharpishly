/**
 * 1. UNIFIED BASE MODEL
 */
class BaseModel {
    constructor(container) {
        // Ensure container is a DOM element
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.viewPath = '';
        this.viewPathSubMenu = '';
    }

    loading() {
        if (!this.container) return;
        this.container.innerHTML = `
            <div class="loader-container">
                <div class="spinner"></div>
                <p class="loader-text">Syncing with Nervous System...</p>
            </div>
        `;
    }

    updateBreadcrumbs() {
        const breadcrumbContainer = document.querySelector('#breadcrumbs');
        if (!breadcrumbContainer) return;

        const path = window.location.pathname;
        const segments = path.split('/').filter(s => s);
        
        let html = '<a href="/" data-link>Home</a>';
        let currentPath = '';

        segments.forEach((segment) => {
            currentPath += `/${segment}`;
            const label = segment.charAt(0).toUpperCase() + segment.slice(1);
            html += ` <span class="separator">/</span> <a href="${currentPath}" data-link>${label}</a>`;
        });

        breadcrumbContainer.innerHTML = html;
    }

    async render() {
        if (!this.viewPath) {
            console.error("View path not defined for", this.constructor.name);
            return;
        }

        this.loading();
        this.updateBreadcrumbs();

        try {
            const response = await fetch(this.viewPath);
            if (!response.ok) throw new Error(`Failed to load: ${response.statusText}`);

            const html = await response.text();
            this.container.innerHTML = html;
            
            if (typeof this.onAfterRender === 'function') {
                this.onAfterRender();
            }
        } catch (error) {
            this.container.innerHTML = `<div class="error-container">⚠️ ${error.message}</div>`;
        }
    }

    // Refactored Submenu logic to be more robust
    async activateSubMenu() {
        const toggle = document.querySelector('.sub-menu-toggle');
        const menu = document.getElementById('quick-tools-menu');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', () => {
            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !isExpanded);
            menu.hidden = isExpanded;
        });

        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                toggle.setAttribute('aria-expanded', 'false');
                menu.hidden = true;
            }
        });
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
        } catch (e) {
            return { h1: "Offline", description: "Heartbeat lost." };
        }
    }
}

class CyberdeckModel extends BaseModel {
    async getData() {
        try {
            const response = await fetch('/php/home/response');
            return await response.json();
        } catch (e) {
            return { h1: "Offline", description: "Heartbeat lost." };
        }
    }

    async streamLogs() {
        const response = await fetch('/php/logs/stream');
        const data = await response.json();
        
        // Render the last 20 lines into a terminal-style block
        const logBox = document.getElementById('log-stream-output');
        logBox.innerHTML = data.lines.map(line => `<div>${line}</div>`).join('');
    }
}

class CsvModel extends BaseModel {
    async getJobs() {
        const response = await fetch('/php/csv/status');
        return await response.json();
    }
}

class LandlordModel extends BaseModel {
    constructor(container) {
        super(container);
        this.viewPath = '/view/landlord/landlord.htm'; 
    }
}

class UploadModel extends BaseModel {
    constructor(container) {
        super(container);
        this.viewPath = '/view/csv/upload.htm';
    }

    attachCsvUploadListener() {
        const form = document.getElementById('csv-upload-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('upload-btn');
            const statusText = document.getElementById('status-text');
            
            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            const formData = new FormData(form);
            try {
                const response = await fetch('/php/csv/upload', { method: 'POST', body: formData });
                const data = await response.json();
                statusText.textContent = data.success ? "Success!" : data.error;
            } catch (err) {
                statusText.textContent = "Upload Failed.";
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Submit to Engine';
            }
        });
    }
}

class OllamaModel extends BaseModel {
    ansiToHtml(text) {
        if (typeof text !== 'string') return text;
        return text.replace(/\[1;32m/g, '<span class="god-green">').replace(/\[0m/g, '</span>');
    }

    async render() {
        this.loading();
        try {
            const response = await fetch('/php/ollama/index');
            const data = await response.json();
            this.container.innerHTML = `
                <div class="ollama-card">
                    <h3>Brain Status: ${data.status}</h3>
                    <blockquote class="code-block">${this.ansiToHtml(data.response)}</blockquote>
                </div>`;
        } catch (e) {
            this.container.innerHTML = `<div class="error">Brain Connection Failed.</div>`;
        }
    }
}

/**
 * 3. CONTROLLERS
 */
class HomeController {
    constructor(container) { this.model = new HomeModel(container); }
    async index() {
        this.model.loading();
        this.model.updateBreadcrumbs();
        const data = await this.model.getData();
        this.model.container.innerHTML = `<h1>${data.h1}</h1><p>${data.description}</p>`;
    }
}

class TenantController {
  constructor(container) {
    this.container = document.querySelector(container);
  }

  async index() {
    this.container.innerHTML = '<div class="loader"></div>';
    
    // Fetch from your optimized PHP backend
    const response = await fetch('/php/crm/tenants');
    const tenants = await response.json();

    const rows = tenants.map(t => `
        <tr>
            <td><strong>${t.name}</strong></td>
            <td>${t.property_name}</td>
            <td><span class="badge ${t.status}">${t.status}</span></td>
            <td class="${t.balance < 0 ? 'text-danger' : 'text-success'}">$${t.balance}</td>
            <td><button class="btn-icon" onclick="notifyTenant(${t.id})">✉️</button></td>
        </tr>
    `).join('');

    document.getElementById('tenant-list-body').innerHTML = rows;
  }
}

class CsvController {
    constructor(container) { this.model = new CsvModel(container); }
    async index() {
        const jobs = await this.model.getJobs();
        // Simplified Table Rendering
        this.model.container.innerHTML = `<h1>CSV Status</h1><ul>${jobs.map(j => `<li>#${j.id}: ${j.status}</li>`).join('')}</ul>`;
    }
}

class UploadController {
    constructor(container) { this.model = new UploadModel(container); }
    async index() {
        await this.model.render();
        this.model.attachCsvUploadListener();
    }
}

class OllamaController {
    constructor(container) { this.model = new OllamaModel(container); }
    async index() { await this.model.render(); }
}

// Placeholder Controllers for Dashboard/Operations
class DashboardController { constructor(c) { this.c = document.querySelector(c); } index() { this.c.innerHTML = "<h1>Dashboard</h1>"; } }
class OperationsController { constructor(c) { this.c = document.querySelector(c); } index() { this.c.innerHTML = "<h1>Operations</h1>"; } }
class IntelligenceController { 
    constructor(c) { this.c = document.querySelector(c); } 
    index() { this.c.innerHTML = "<h1>Intelligence</h1>"; }
    //
    async handleAiQuery(query) {
        const response = await fetch('/php/ai/ask', {
            method: 'POST',
            body: JSON.stringify({ prompt: query })
        });
        const data = await response.json();

        // Show the "Thinking" process
        document.getElementById('ai-context-debug').innerHTML = `
            <details>
                <summary>🔍 View Injected Context</summary>
                <pre>${JSON.stringify(data.context, null, 2)}</pre>
            </details>
        `;
        
        document.getElementById('ai-response-body').innerHTML = data.answer;
    }
    // 
}

/**
 * 4. ROUTER (STABILIZED)
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

    async loadRoute() {
        if (this.activeInterval) clearInterval(this.activeInterval);
        
        const path = window.location.pathname; // FIXED: Define path first
        const subNav = document.querySelector('#sub-nav');

        // Layout mapping
        const subMenuMap = {
            '/operations': '/view/layout/sub-ops.htm',
            '/csv-upload': '/view/layout/sub-ops.htm',
            '/csv': '/view/layout/sub-ops.htm',
            '/intelligence': '/view/layout/sub-ai.htm',
            '/cyberdeck': '/view/layout/sub-cyber.htm',
            '/scanner': '/view/layout/sub-cyber.htm',
            '/cyberdeck/vpn': '/view/layout/sub-cyber.htm',
            '/cyberdeck/terminal': '/view/layout/sub-cyber.htm',
            '/landlord': '/view/layout/sub-crm.htm',
            '/crm/tenants': '/view/layout/sub-crm.htm',
            '/crm/maintenance': '/view/layout/sub-crm.htm',
            '/crm/finance': '/view/layout/sub-crm.htm',
        };

        // Handle Sub-nav Injection
        if (subNav) {
            if (subMenuMap[path]) {
                try {
                    const res = await fetch(subMenuMap[path]);
                    subNav.innerHTML = await res.text();
                } catch (e) { subNav.innerHTML = ''; }
            } else {
                subNav.innerHTML = '';
            }
        }

        const ControllerClass = this.routes[path] || HomeController;
        const controller = new ControllerClass(this.container);
        controller.index();

        // Polling logic
        if (path === '/csv') {
            this.activeInterval = setInterval(() => controller.index(), 5000);
        }
    }
}

const routes = {
    "/": HomeController,
    "/dashboard": DashboardController,
    "/operations": OperationsController,
    "/intelligence": IntelligenceController,
    "/csv-upload": UploadController,
    "/ollama": OllamaController
};



document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    }
    new Router(routes);
});