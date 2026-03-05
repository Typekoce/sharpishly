#!/bin/bash

# --- 1. TARGETING EXISTING ARCHITECTURE ---
echo "🌌 Integrating OpenClaw into existing PHP/Website structure..."
# We use your existing 'website' for front-end and 'php/src' for back-end
mkdir -p website/js website/css php/src/Agents storage/queue storage/vault logs

# --- 2. SECURITY: MASTER ENCRYPTION KEY ---
if [ ! -f storage/vault/master.key ]; then
    openssl rand -base64 32 > storage/vault/master.key
    echo "🔑 Master Encryption Key generated in storage/vault/"
fi

# --- 3. THE UI: CYBERPUNK CONTROL CENTER ---
# Saving to website/css/style.css
cat <<EOF > website/css/style.css
:root { --bg: #0a0c10; --panel: #161b22; --blue: #58a6ff; --green: #3fb950; --red: #f85149; --txt: #c9d1d9; }
body { background: var(--bg); color: var(--txt); font-family: 'Segoe UI', sans-serif; margin: 0; overflow: hidden; }
#app { display: grid; grid-template-columns: 280px 1fr 350px; height: 100vh; }
aside, main { border-right: 1px solid #30363d; padding: 20px; overflow-y: auto; }
.card { background: var(--panel); border: 1px solid #30363d; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
.agent-status { display: flex; align-items: center; gap: 10px; font-size: 0.9rem; margin-bottom: 10px; }
.dot { width: 8px; height: 8px; border-radius: 50%; background: #333; }
.dot.online { background: var(--green); box-shadow: 0 0 8px var(--green); }
#progress-bar { position: fixed; top: 0; left: 0; height: 4px; background: var(--blue); width: 0%; transition: 0.3s; z-index: 100; }
#log-stream { font-family: 'Consolas', monospace; font-size: 11px; color: #8b949e; }
.log-entry { margin-bottom: 5px; border-left: 2px solid #333; padding-left: 8px; }
button { width: 100%; padding: 10px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; margin-top: 8px; transition: 0.2s; }
button:hover { filter: brightness(1.2); }
.btn-stop { background: var(--red); color: white; }
.btn-run { background: var(--blue); color: white; }
.btn-scout { background: var(--green); color: white; }
EOF

# --- 4. THE FRONTEND LOGIC (MVC Engine) ---
# Saving to website/js/script.js
cat <<EOF > website/js/script.js
class OpenClawSystem {
    constructor() {
        // Updated path to your PHP folder
        this.sse = new EventSource('/php/nervous_system.php?action=stream');
        this.init();
    }
    init() {
        this.sse.onmessage = (e) => {
            const data = JSON.parse(e.data);
            if (data.event === 'PROGRESS') document.getElementById('progress-bar').style.width = data.val + '%';
            this.renderLog(data.msg);
        };
    }
    renderLog(msg) {
        const div = document.createElement('div');
        div.className = 'log-entry';
        div.innerHTML = \`[\${new Date().toLocaleTimeString()}] \${msg}\`;
        const container = document.getElementById('log-stream');
        if(container) container.prepend(div);
    }
    async triggerTask(task) {
        this.renderLog("Initiating: " + task);
        await fetch('/php/nervous_system.php?action=queue&task=' + task);
    }
    async toggleSafety(sig) {
        await fetch('/php/nervous_system.php?action=safety&sig=' + sig);
        this.renderLog("SAFETY SIGNAL: " + sig.toUpperCase());
    }
}
const App = new OpenClawSystem();
EOF

# --- 5. THE NERVOUS SYSTEM (Integrating into php/src) ---
cat <<EOF > php/src/Vault.php
<?php
namespace App;
class Vault {
    public static function getKey() { return base64_decode(file_get_contents(__DIR__ . '/../../storage/vault/master.key')); }
    public static function encrypt(\$data) {
        \$key = self::getKey(); \$iv = openssl_random_pseudo_bytes(16);
        \$enc = openssl_encrypt(\$data, 'aes-256-cbc', \$key, 0, \$iv);
        return base64_encode(\$enc . '::' . \$iv);
    }
    public static function decrypt(\$data) {
        \$key = self::getKey(); list(\$d, \$iv) = explode('::', base64_decode(\$data), 2);
        return openssl_decrypt(\$d, 'aes-256-cbc', \$key, 0, \$iv);
    }
}
EOF

# Dashboard route handler
cat <<EOF > php/nervous_system.php
<?php
// This acts as the bridge for your website/index.html
header('Access-Control-Allow-Origin: *');
\$action = \$_GET['action'] ?? '';

if (\$action === 'queue') {
    \$task = \$_GET['task'];
    file_put_contents(__DIR__ . "/../storage/queue/".uniqid().".job", json_encode(["task" => \$task]));
    exit;
}
if (\$action === 'safety') {
    \$sig = \$_GET['sig'];
    if (\$sig === 'stop') file_put_contents(__DIR__ . '/../storage/queue/STOP', '1');
    else @unlink(__DIR__ . '/../storage/queue/STOP');
    exit;
}
if (\$action === 'stream') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    while (true) {
        if (file_exists(__DIR__ . '/../storage/queue/progress.json')) {
            echo "data: " . file_get_contents(__DIR__ . '/../storage/queue/progress.json') . "\n\n";
        }
        ob_flush(); flush(); sleep(1);
    }
}
EOF

# --- 6. THE MASTER WORKER ---
# This replaces or works alongside your worker-daemon.php
cat <<EOF > php/src/worker-daemon.php
<?php
require_once __DIR__ . '/autoload.php';
use App\Db;

echo "🤖 OpenClaw Worker Daemon Online...\n";

// Handle DB Connection with retry logic for Docker
\$db = null;
while (\$db === null) {
    try {
        \$db = new Db();
    } catch (\Exception \$e) {
        echo "⏳ Waiting for Database... \n";
        sleep(5);
    }
}

while (true) {
    if (file_exists(__DIR__ . '/../../storage/queue/STOP')) { sleep(2); continue; }
    
    \$jobs = glob(__DIR__ . '/../../storage/queue/*.job');
    foreach (\$jobs as \$f) {
        \$job = json_decode(file_get_contents(\$f), true);
        \$task = \$job['task'];
        
        file_put_contents(__DIR__ . '/../../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>50, "msg"=>"Executing \$task..."]));
        
        // ADD YOUR CUSTOM TASK LOGIC HERE
        sleep(2); 
        
        file_put_contents(__DIR__ . '/../../storage/queue/progress.json', json_encode(["event"=>"PROGRESS", "val"=>100, "msg"=>"Success: \$task"]));
        unlink(\$f);
        @unlink(__DIR__ . '/../../storage/queue/progress.json');
    }
    sleep(1);
}
EOF

# --- 7. PERMISSIONS ---
chmod -R 777 storage/queue storage/vault logs php/src
echo "🚀 OPENCLAW INTEGRATED SUCCESSFULLY."
echo "Your dashboard is at website/index.html"
echo "Your worker is at php/src/worker-daemon.php"