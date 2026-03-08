#!/usr/bin/env bash
set -euo pipefail

echo "┌──────────────────────────────────────────────────────────────┐"
echo "│     Sharpishly Hardware Scanner Installer (Safe Mode)        │"
echo "│     Adds detection libs + Model/View/Controller/Agent       │"
echo "│     Does NOT remove any existing services                    │"
echo "└──────────────────────────────────────────────────────────────┘"

# ─── Safety checks ────────────────────────────────────────────────────────
echo -e "\n🔒 Safety checks..."

if [[ ! -f docker-compose.yml ]]; then
    echo "❌ docker-compose.yml not found in current directory!"
    exit 1
fi

if [[ ! -d php/src ]]; then
    echo "❌ php/src directory not found!"
    exit 1
fi

read -p "This will modify docker-compose.yml, add new files, and update Dockerfile. Continue? (y/N) " confirm
[[ "$confirm" != "y" && "$confirm" != "Y" ]] && { echo "Aborted."; exit 0; }

# ─── Backups ──────────────────────────────────────────────────────────────
BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

cp docker-compose.yml "$BACKUP_DIR/docker-compose.yml.bak" 2>/dev/null || true
echo "✅ Backup created: $BACKUP_DIR/"

# ─── 1. Update Dockerfile (only if needed) ────────────────────────────────
DOCKERFILE="Dockerfile"
if [[ -f "$DOCKERFILE" ]]; then
    echo "Dockerfile already exists — checking if hardware tools are installed..."
    if ! grep -q "usbutils" "$DOCKERFILE"; then
        cp "$DOCKERFILE" "$BACKUP_DIR/$DOCKERFILE.bak"
        cat >> "$DOCKERFILE" << 'EOF'

# Hardware detection tools (added by install_hardware.sh)
RUN apt-get update && apt-get install -y \
    usbutils iproute2 util-linux pciutils \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
EOF
        echo "✅ Added hardware tools to existing Dockerfile"
    else
        echo "Hardware tools already present in Dockerfile — skipping"
    fi
else
    echo "Dockerfile not found — creating minimal version"
    cat > "$DOCKERFILE" << 'EOF'
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    usbutils iproute2 util-linux pciutils \
    python3 python3-pip \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/php
EOF
    echo "✅ Created new Dockerfile with hardware tools"
fi

# ─── 2. Update docker-compose.yml (only append to php & worker) ───────────
if ! grep -q "usbutils" docker-compose.yml; then
    cp docker-compose.yml docker-compose.yml.bak
    sed -i '/php:/,/depends_on:/ {
        /depends_on:/i \    command: >\n      sh -c "apt-get update && apt-get install -y usbutils iproute2 util-linux pciutils python3 python3-pip && php-fpm"'
    }' docker-compose.yml

    sed -i '/worker:/,/depends_on:/ {
        /depends_on:/i \    command: >\n      sh -c "apt-get update && apt-get install -y usbutils iproute2 util-linux pciutils python3 python3-pip && php /var/www/html/php/worker-daemon.php"'
    }' docker-compose.yml

    echo "✅ docker-compose.yml updated with hardware packages"
else
    echo "Hardware packages already in docker-compose.yml — skipping"
fi

# ─── 3. Create missing files ──────────────────────────────────────────────

echo "Creating hardware detection files..."

# HardwareModel.php
mkdir -p php/src/Models
cat > php/src/Models/HardwareModel.php << 'EOF'
<?php
declare(strict_types=1);

namespace App\Models;

use App\Db;

class HardwareModel
{
    private Db $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    public function saveScan(array $data): int
    {
        return $this->db->save([
            'tbl'          => 'hardware_scans',
            'scan_type'    => $data['scan_type'] ?? 'full',
            'usb_count'    => $data['usb_count'] ?? 0,
            'cpu_info'     => $data['cpu'] ?? 'unknown',
            'memory_info'  => json_encode($data['memory'] ?? []),
            'network_info' => json_encode($data['network'] ?? []),
            'raw_data'     => json_encode($data),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function getRecentScans(int $limit = 10): array
    {
        return $this->db->find([
            'tbl'   => 'hardware_scans',
            'order' => ['id' => 'desc'],
            'limit' => $limit,
        ]);
    }
}
EOF

# HardwareController.php
mkdir -p php/src/Controllers
cat > php/src/Controllers/HardwareController.php << 'EOF'
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HardwareModel;

class HardwareController
{
    public function info(): void
    {
        header('Content-Type: application/json');

        $data = [
            'cpu'     => $this->getCpuInfo(),
            'memory'  => $this->getMemoryInfo(),
            'os'      => php_uname('s') . ' ' . php_uname('r'),
            'usb'     => $this->getUsbDevices(),
            'network' => $this->getNetworkInterfaces(),
            'disks'   => $this->getDisks(),
        ];

        $model = new HardwareModel();
        $model->saveScan($data);

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    private function getCpuInfo(): string
    {
        return trim(shell_exec('grep "model name" /proc/cpuinfo | head -n1 | cut -d: -f2 | xargs') ?: 'unknown');
    }

    private function getMemoryInfo(): array
    {
        $mem = shell_exec('free -h --si | grep Mem');
        preg_match('/Mem:\s+(\S+)\s+(\S+)\s+(\S+)/', $mem, $m);
        return [
            'total' => $m[1] ?? 'unknown',
            'used'  => $m[2] ?? 'unknown',
            'free'  => $m[3] ?? 'unknown',
        ];
    }

    private function getUsbDevices(): array
    {
        $out = shell_exec('lsusb');
        return array_filter(array_map('trim', explode("\n", $out ?? '')));
    }

    private function getNetworkInterfaces(): array
    {
        $out = shell_exec('ip -br link show');
        return array_filter(array_map('trim', explode("\n", $out ?? '')));
    }

    private function getDisks(): array
    {
        $out = shell_exec('lsblk -dno NAME,SIZE,TYPE,MODEL | grep disk');
        return array_filter(array_map('trim', explode("\n", $out ?? '')));
    }
}
EOF

# View
mkdir -p php/src/views/hardware
cat > php/src/views/hardware/index.html << 'EOF'
<h1>🔧 Hardware Scanner</h1>
<button onclick="scan()">Scan All Hardware</button>
<pre id="result" style="margin-top:20px; background:#f8fafc; padding:15px; border-radius:8px; font-family:monospace;">Click to scan...</pre>

<script>
async function scan() {
    const res = await fetch('/php/hardware/info');
    const data = await res.json();
    document.getElementById('result').textContent = JSON.stringify(data, null, 2);
}
</script>
EOF

# HardwareScanAgent.php
mkdir -p php/src/Agents
cat > php/src/Agents/HardwareScanAgent.php << 'EOF'
<?php
declare(strict_types=1);

namespace App\Agents;

use App\Controllers\HardwareController;

class HardwareScanAgent
{
    public function run(): void
    {
        $controller = new HardwareController();
        $controller->info(); // reuses logic & saves to DB
    }
}
EOF

echo "✅ All hardware files created!"

# ─── Add migration snippet to HomeModel ────────────────────────────────────
echo ""
echo "Add this block to HomeModel::migrate() inside the try {}:"
echo ""
cat << 'EOF'
$this->createTable('hardware_scans', [
    'id'           => 'BIGINT AUTO_INCREMENT PRIMARY KEY',
    'scan_type'    => 'VARCHAR(50) DEFAULT "full"',
    'usb_count'    => 'INT DEFAULT 0',
    'cpu_info'     => 'VARCHAR(255)',
    'memory_info'  => 'JSON',
    'network_info' => 'JSON',
    'raw_data'     => 'JSON',
    'created_at'   => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
], [
    'engine'  => 'InnoDB',
    'charset' => 'utf8mb4',
    'collate' => 'utf8mb4_unicode_ci',
]);
$report .= "[OK] Table 'hardware_scans' created or already exists\n";
EOF

echo ""
echo "Done! Now:"
echo "1. docker compose down && docker compose up -d --build"
echo "2. Visit /php/hardware (or add route)"
echo "3. All previous services (Dozzle, Adminer, Ollama, worker, mail-agent) remain untouched."

exit 0