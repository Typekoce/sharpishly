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

class CrmModel extends BaseModel {
    constructor(container) {
        super(container);
    }

    async getTenants() {
        try {
            const response = await fetch('/php/crm/tenants');
            if (!response.ok) throw new Error("CRM Data unreachable");
            return await response.json();
        } catch (e) {
            console.error("CrmModel Error:", e);
            return [];
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

class CrmController {
    constructor(c) {
        this.c = document.querySelector(c);
        this.model = new CrmModel(this.c);
    }

    async index() {
        this.model.loading();
        this.model.updateBreadcrumbs();

        const tenants = await this.model.getTenants();
        
        this.render(tenants);
    }

    render(tenants) {
        const rows = tenants.length > 0 
            ? tenants.map(t => `
                <tr>
                    <td><strong>${t.name}</strong></td>
                    <td>${t.property || 'N/A'}</td>
                    <td><span class="status-badge ${t.status}">${t.status}</span></td>
                    <td class="${t.balance < 0 ? 'text-danger' : 'text-success'}">$${t.balance}</td>
                    <td><button class="btn-sm" onclick="alert('Opening File for ${t.name}')">VIEW</button></td>
                </tr>
            `).join('')
            : '<tr><td colspan="5">No tenant records found in the database.</td></tr>';

        this.c.innerHTML = `
            <div class="crm-container">
                <div class="header-actions">
                    <h1>CRM / Tenant Manager</h1>
                    <button class="btn-primary" onclick="location.href='/csv-upload'" data-link>Import Leads</button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Property</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;
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
        // This ensures 'this.c' is the actual DOM element
        this.c = document.querySelector(c);
        this.endpoint = '/php/home/response';
    }

    async index() {
        // 1. Immediate visual feedback
        this.c.innerHTML = `
            <div class="dashboard-wrapper">
                <h1>Dashboard</h1>
                <p class="status-text">Synchronizing with Neural Link...</p>
                <div class="loader-mini"></div>
            </div>
        `;

        try {
            // 2. The Handshake: Fetching data from your working PHP endpoint
            const response = await fetch(this.endpoint);
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            
            const data = await response.json();

            // 3. The Injection: Replace loading text with the real data
            this.render(data);
        } catch (error) {
            console.error("Dashboard Wire Failure:", error);
            this.c.innerHTML = `
                <h1>Dashboard</h1>
                <div class="error-box">
                    <p>🚨 CONNECTION SEVERED</p>
                    <code>${error.message}</code>
                </div>
            `;
        }
    }

    render(data) {
        // This maps the JSON response to your dashboard cards
        this.c.innerHTML = `
            <div class="dashboard-container">
                <h1>System Overview</h1>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="stat-card" style="border: 1px solid #333; padding: 20px; background: #111;">
                        <h3 style="color: #00ff00;">STATUS</h3>
                        <p style="font-family: monospace; font-size: 1.2rem;">${data.status || 'ACTIVE'}</p>
                    </div>
                    <div class="stat-card" style="border: 1px solid #333; padding: 20px; background: #111;">
                        <h3 style="color: #00ff00;">PULSE</h3>
                        <p style="font-family: monospace;">${data.timestamp || 'N/A'}</p>
                    </div>
                    <div class="stat-card" style="border: 1px solid #333; padding: 20px; background: #111;">
                        <h3 style="color: #00ff00;">INTEL</h3>
                        <p>${data.message || 'Systems Nominal'}</p>
                    </div>
                </div>
            </div>
        `;
    }
}

class OperationsController {
    constructor(c) {
        this.c = document.querySelector(c);
        this.endpoint = '/php/csv/status'; // Targeting your CSV job engine
    }

    async index() {
        this.c.innerHTML = `
            <div class="operations-header">
                <h1>Operations Control</h1>
                <p class="status-badge pulse">Scanning Background Tasks...</p>
            </div>
            <div id="ops-display" class="ops-grid"></div>
        `;

        try {
            const response = await fetch(this.endpoint);
            if (!response.ok) throw new Error(`Operational Link Failure: ${response.status}`);
            
            const data = await response.json();
            this.render(data);
        } catch (error) {
            document.getElementById('ops-display').innerHTML = `
                <div class="alert alert-error">
                    <strong>CRITICAL ERROR:</strong> Unable to reach Operations Engine.
                    <br><code>${error.message}</code>
                </div>
            `;
        }
    }

    render(data) {
        const display = document.getElementById('ops-display');
        
        // Check if we have active jobs in the response
        const jobList = data.jobs && data.jobs.length > 0 
            ? data.jobs.map(job => `
                <div class="job-row" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #222;">
                    <span>Job #${job.id}</span>
                    <span class="status-${job.status}">${job.status.toUpperCase()}</span>
                </div>
            `).join('')
            : '<p style="padding: 20px; color: #666;">No active background processes detected.</p>';

        display.innerHTML = `
            <div class="ops-card" style="background: #0a0a0a; border: 1px solid #333; margin-top: 20px;">
                <div class="card-header" style="background: #1a1a1a; padding: 10px; border-bottom: 1px solid #333;">
                    <h2 style="font-size: 1rem; color: #00ff00; margin: 0;">Task Queue Status</h2>
                </div>
                <div class="card-body">
                    ${jobList}
                </div>
                <div class="card-footer" style="padding: 10px; text-align: right;">
                    <button onclick="location.href='/csv-upload'" data-link class="btn-small">Launch New Job</button>
                </div>
            </div>
        `;
    }
}


class IntelligenceController { 
    constructor(c) { 
        this.c = document.querySelector(c); 
    } 

    index() { 
        this.c.innerHTML = `
            <div class="intelligence-hub">
                <h1>Intelligence Hub</h1>
                <div id="ai-context-debug" class="debug-panel"></div>
                <div class="terminal-box" id="ai-response-body" style="min-height: 200px; background: #000; color: #0f0; padding: 15px; margin-bottom: 15px; font-family: monospace;">
                    [READY] Waiting for Neural Input...
                </div>
                <div class="input-group">
                    <input type="text" id="ai-query-input" placeholder="Query Intelligence Engine..." style="width: 70%; padding: 10px;">
                    <button id="ai-execute-btn" style="padding: 10px 20px; cursor: pointer;">EXECUTE</button>
                </div>
            </div>
        `; 

        // Wire the local button click to the class method
        const btn = document.getElementById('ai-execute-btn');
        const input = document.getElementById('ai-query-input');

        btn.addEventListener('click', () => {
            this.handleAiQuery(input.value);
            input.value = ''; // Clear after sending
        });
    }

    async handleAiQuery(query) {
        if (!query) return;
        
        const responseBody = document.getElementById('ai-response-body');
        responseBody.innerHTML = "Thinking...";

        try {
            const response = await fetch('/php/ai/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: query })
            });
            const data = await response.json();

            // Show the "Thinking" process (Context injection)
            const debugContainer = document.getElementById('ai-context-debug');
            if (debugContainer) {
                debugContainer.innerHTML = `
                    <details style="margin-bottom: 10px; color: #888;">
                        <summary>🔍 View Injected Context</summary>
                        <pre style="font-size: 11px;">${JSON.stringify(data.context || 'No context found', null, 2)}</pre>
                    </details>
                `;
            }
            
            responseBody.innerHTML = data.answer || "No response received.";
        } catch (error) {
            responseBody.innerHTML = `<span class="text-danger">🚨 Link Error: ${error.message}</span>`;
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
            '/crm': '/view/layout/sub-crm.htm', // Add this line
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
    "/csv": CsvController, // Added
    "/ollama": OllamaController,
    "/vision": VisionController, // Added (The phone stream)
    "/landlord": TenantController, // Added (Mapping landlord to Tenant list)
    "/crm":CrmController
};



document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (toggle && navLinks) {
        toggle.addEventListener('click', () => navLinks.classList.toggle('active'));
    }
    new Router(routes);
});