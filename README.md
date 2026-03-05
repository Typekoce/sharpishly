# Sharpishly Research & Development

Minimal, modern, dependency-free responsive navbar + landing page.

> Research and development accessible to everyone  
> © Sharpishly Research and Development

## Features

- Pure HTML + CSS + JavaScript (no framework, no Bootstrap)
- Responsive navbar with dropdown menus
- Mobile offcanvas menu (slide-in from right)
- Hover + click support for dropdowns on desktop
- Accessible keyboard navigation (Esc to close mobile menu)
- Very lightweight (~4 KB minified JS + CSS combined)
- Custom Bootstrap-inspired color variables and styling

## Technologies

- HTML5
- CSS (custom properties / variables, modern layout)
- Vanilla JavaScript (ES6+)

## Project structure

```website/
├── index.html
├── styles.css
├── script.js
├── README.md
├── CONTRIBUTORS.md
├── CHANGELOG.md
└── ROADMAP.md

```

# Website Live Preview
[Sharpishly R&D](https://html-preview.github.io/?url=https://github.com/Typekoce/sharpishly/blob/main/website/index.html)

# Sharpishly Research & Development

Minimal, modern, dependency-free responsive navbar + landing page.

## Technologies
- HTML5 / CSS3 / Vanilla JS
- **PHP 8.2 (FPM)**
- **Nginx (Web Server & Reverse Proxy)**
- **Docker & Docker Compose**

## Project structure
```website/
├── website/            # Frontend assets (HTML, CSS, JS)
├── php/                # Backend logic
│   ├── src/            # MVC Core & Controllers
│   ├── logs/           # Application logging
│   └── index.php       # PHP Front Controller
├── nginx.conf          # Reverse Proxy Configuration
├── Dockerfile          # Nginx container build
└── docker-compose.yml  # Multi-container orchestration

This README update reflects your transition from a simple static landing page to a sophisticated, **Agent-driven MVC Ecosystem**. It highlights the new "God Mode" architecture, the secure Vault, and the Dockerized worker environment.

---

# Sharpishly Research & Development

Minimal, modern, dependency-free responsive navbar + landing page, now evolved into a high-performance **Agentic MVC Ecosystem**.

> Research and development accessible to everyone
> © Sharpishly Research and Development

## 🚀 Evolution: God Mode

Sharpishly has transitioned from a static site to a fully autonomous system. It now features background agents for SEO, social media automation, and web research, all managed through a unified Cyberpunk-style Command Center.

## ✨ Key Features

* **Pure Frontend:** Dependency-free HTML5/CSS3/Vanilla JS UI.
* **Agentic Nervous System:** Real-time task streaming via PHP SSE (Server-Sent Events).
* **Secure Vault:** AES-256-CBC encryption for sensitive API keys and credentials.
* **Autonomous Workers:** Multi-threaded background daemons for handling high-stakes tasks.
* **Dockerized Infrastructure:** Fully containerized Nginx, PHP-FPM, MySQL, and Worker services.

## 🛠️ Technologies

* **PHP 8.2 (FPM):** Core MVC logic and background daemons.
* **Nginx:** Web server and high-performance reverse proxy.
* **MySQL 8.0:** Persistent data storage with integrated health checks.
* **Docker & Compose:** Seamless multi-container orchestration.
* **OpenClaw Engine:** Custom-built agent reasoning and task queuing logic.

## 📂 Project Structure

```text
.
├── website/                # Cyberpunk Dashboard & Frontend assets
│   ├── dashboard.html      # Agent Command Center
│   ├── index.html          # Main Landing Page
│   └── js/script.js        # SSE-driven Nervous System logic
├── php/                    # Backend logic
│   ├── nervous_system.php  # Task Queue & SSE bridge
│   └── src/
│       ├── Agents/         # Autonomous Scout & Social agents
│       ├── Controllers/    # MVC Routing & Business logic
│       ├── Vault.php       # AES-256 Encryption Service
│       └── worker-daemon.php # Background Task Processor
├── shells/                 # Automated DevOps & Maintenance scripts
├── storage/                # Persistant Vault & Job Queues
├── nginx.conf              # Proxy & Route Configuration
├── Dockerfile              # Container definitions
└── docker-compose.yml      # Orchestration of the God Mode stack

```

## 🛠️ Getting Started

1. **Initialize Environment:**
```bash
chmod +x shells/*.sh
./shells/setup_openclaw.sh

```


2. **Launch Ecosystem:**
```bash
docker-compose up -d

```


3. **Access Dashboard:**
Navigate to `http://192.168.0.11:8080/dashboard.html` to witness the Thought Stream.

## 📊 Monitoring

* **Logs:** View real-time container logs via Dozzle at `http://192.168.0.11:8082`.
* **Database:** Manage MySQL schemas via Adminer at `http://192.168.0.11:8081`.

---

**Commit Message for this update:**
`docs(readme): update project documentation to reflect Agentic MVC architecture`

**Would you like me to add a "Roadmap" section to the README detailing the upcoming AI integration for your Scout Agent?**