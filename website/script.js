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

    /**
     * Appends a new line to the terminal and prunes to prevent browser lag.
     * Use this for streaming updates or manual log injections.
     */
    updateTerminal(newLine) {
        const terminal = this.container.querySelector('.code-block');
        if (!terminal) return;

        // Wrap the line in a div for easier pruning
        const lineHtml = `<div>${this.ansiToHtml(newLine)}</div>`;
        terminal.insertAdjacentHTML('beforeend', lineHtml);
        
        // Prune logic: Keep the last 50 entries
        while (terminal.children.length > 50) {
            terminal.removeChild(terminal.firstChild);
        }

        // Auto-scroll to the latest entry
        terminal.scrollTop = terminal.scrollHeight;
    }

    async render() {
        this.loading();
        try {
            const response = await fetch('/php/ollama/index');
            const data = await response.json();
            
            // Initial render creates the terminal structure
            this.container.innerHTML = `
                <div class="ollama-card">
                    <div class="card-header">
                        <h3>Brain Status: <span class="status-badge ${data.status}">${data.status}</span></h3>
                    </div>
                    <blockquote class="code-block" id="log-output" style="max-height: 400px; overflow-y: auto; display: flex; flex-direction: column;">
                        ${this.ansiToHtml(data.response)}
                    </blockquote>
                </div>`;
            
            // Ensure the initial content is scrolled to bottom
            const terminal = this.container.querySelector('.code-block');
            if (terminal) terminal.scrollTop = terminal.scrollHeight;

        } catch (e) {
            this.container.innerHTML = `
                <div class="ollama-card border-error">
                    <p class="error">⚠️ Brain Connection Failed: ${e.message}</p>
                </div>`;
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

class VisionController {
    constructor(container) {
        this.container = container;
    }

    index() {
        this.container.innerHTML = `
            <div class="vision-card">
                <h3><span class="god-green">[LIVE]</span> External Sensory Node 01</h3>
                <div class="stream-container">
                    <img src="http://localhost:5001/video_feed" style="width: 100%; border: 2px solid #00ff00;">
                </div>
                <div class="controls">
                    <button onclick="alert('Snapshot Saved to /storage/uploads')">CAPTURE</button>
                </div>
            </div>
        `;
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
class DashboardController {
    constructor(c) {
        this.c = document.querySelector(c);
        this.endpoint = '/php/home/response'; // Reusing your successful endpoint
    }

    async index() {
        // 1. Visual Feedback
        this.c.innerHTML = "<h1>Dashboard</h1><p class='status'>Syncing with Neural Link...</p>";

        try {
            // 2. The Wire (Fetching the data)
            const response = await fetch(this.endpoint);
            const data = await response.json();

            // 3. The Injection (Rendering the data)
            this.render(data);
        } catch (error) {
            this.c.innerHTML = `<h1>Dashboard</h1><p class='error'>Connection Failed: ${error.message}</p>`;
        }
    }

    render(data) {
        // This takes the JSON from PHP and builds the UI
        this.c.innerHTML = `
            <h1>Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>System Status</h3>
                    <p class="status-online">${data.status || 'Active'}</p>
                </div>
                <div class="stat-card">
                    <h3>Last Pulse</h3>
                    <p>${data.timestamp || 'Just now'}</p>
                </div>
                <div class="stat-card">
                    <h3>Message</h3>
                    <p>${data.message || 'Systems Nominal'}</p>
                </div>
            </div>
        `;
    }
}
class OperationsController { constructor(c) { this.c = document.querySelector(c); } index() { this.c.innerHTML = "<h1>Operations</h1>"; } }
class IntelligenceController extends BaseController {
    index() {
        this.render(`
            <div class="intelligence-hub">
                <h2>Intelligence Engine (Ollama)</h2>
                <div id="ai-chat-output" class="terminal-box" style="height: 300px; overflow-y: auto; background: #000; color: #0f0; padding: 10px; font-family: monospace;">
                    [READY] System listening...
                </div>
                <div class="input-group" style="margin-top: 10px;">
                    <input type="text" id="ai-query-input" placeholder="Enter query..." style="width: 80%; background: #111; color: #fff; border: 1px solid #333; padding: 10px;">
                    <button id="ai-send-btn" style="padding: 10px 20px; background: #00ff00; color: #000; font-weight: bold; border: none; cursor: pointer;">EXECUTE</button>
                </div>
            </div>
        `);
        
        // Wire the button
        document.getElementById('ai-send-btn').addEventListener('click', () => this.handleAiQuery());
    }

    async handleAiQuery() {
        const input = document.getElementById('ai-query-input');
        const output = document.getElementById('ai-chat-output');
        const query = input.value;

        if (!query) return;

        output.innerHTML += `\n<div>> ${query}</div>`;
        input.value = '';

        try {
            const response = await fetch('/php/ai/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: query })
            });
            const data = await response.json();
            output.innerHTML += `<div class="god-green">AI: ${data.answer}</div>`;
            output.scrollTop = output.scrollHeight;
        } catch (e) {
            output.innerHTML += `<div class="error">🚨 Neural link failed: ${e.message}</div>`;
        }
    }
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

// const routes = {
//     "/": HomeController,
//     "/dashboard": DashboardController,
//     "/operations": OperationsController,
//     "/intelligence": IntelligenceController,
//     "/csv-upload": UploadController,
//     "/ollama": OllamaController
// };

const routes = {
    "/": HomeController,
    "/dashboard": DashboardController,
    "/operations": OperationsController,
    "/intelligence": IntelligenceController,
    "/csv-upload": UploadController,
    "/csv": CsvController, // Added
    "/ollama": OllamaController,
    "/vision": VisionController, // Added (The phone stream)
    "/landlord": TenantController // Added (Mapping landlord to Tenant list)
};



document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    }
    new Router(routes);
});