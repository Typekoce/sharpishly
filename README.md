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