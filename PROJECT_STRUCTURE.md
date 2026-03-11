# Sharpishly OS - Infrastructure Map

## 🏗️ Core Orchestration
- `Makefile`: Single entry point for `deploy`, `backup`, and `logs`.
- `docker-compose.yml`: Defines the PHP-FPM, Nginx, and Python bridge.
- `scripts/`: Production safety gates (`provision-check.sh`) and B2 Offloading (`cloud-backup.sh`).

## 🧠 Backend (PHP 8.x)
- `php/src/Controllers/`: MVC entry points (Ollama, CSV, CRM, Cyberdeck).
- `php/src/Services/`: The "Nervous System."
    - `Logger.php`: Multi-channel logging with Telegram/Axiom offloading.
    - `StorageService.php`: Atomic file operations for the `/storage` volume.
    - `Vault.php`: Encryption and sensitive key management.
- `php/bin/`: Long-running background workers (`ai-worker.php`).

## 💾 Persistent Storage (Volume Mounts)
- `storage/logs/`: Centralized `app.log` and sub-channel logs.
- `storage/vault/`: Master keys and encrypted secrets.
- `storage/vpn-config/`: WireGuard peer identities and server configs.

## 🎨 Frontend (SPA)
- `website/js/script.js`: Single Page Application router and Controller logic.
- `website/styles.css`: Hybrid "Management UI" + "God Mode" Terminal theme.
- `website/view/`: Smarty/HTML templates organized by pillar.