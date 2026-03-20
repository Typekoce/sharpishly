#!/usr/bin/env bash

# bake.sh - Quick generator for MVC triad in your PHP project
# Usage: ./bake.sh <resource_name>   (e.g. ./bake.sh user)
#        ./bake.sh user --force      (overwrite if files exist)

set -euo pipefail

RESOURCE="${1:-}"
FORCE=""

if [[ -z "$RESOURCE" ]]; then
    echo "Usage: $0 <resource_name> [--force]"
    echo "Example:"
    echo "  ./bake.sh user"
    echo "  ./bake.sh orderItem --force"
    exit 1
fi

# Handle optional --force flag
if [[ "${2:-}" == "--force" ]]; then
    FORCE="--force"
fi

# Convert to different naming conventions
RESOURCE_LOWER=$(echo "$RESOURCE" | tr '[:upper:]' '[:lower:]')
RESOURCE_UPPER=$(echo "$RESOURCE" | tr '[:lower:]' '[:upper:]')
RESOURCE_CAMEL=$(echo "$RESOURCE" | awk '{print toupper(substr($0,1,1)) tolower(substr($0,2))}')
RESOURCE_PASCAL="$RESOURCE_CAMEL"   # same as camel for now

# Directory structure (adjust if your paths are different)
MODEL_DIR="php/src/Models"
CONTROLLER_DIR="php/src/Controllers"
VIEW_DIR="php/src/views/${RESOURCE_LOWER}"

# Create directories if they don't exist
mkdir -p "$MODEL_DIR"
mkdir -p "$CONTROLLER_DIR"
mkdir -p "$VIEW_DIR"

echo "Generating files for resource: $RESOURCE_LOWER"

# ────────────────────────────────────────────────────────────────
# 1. Model
# ────────────────────────────────────────────────────────────────
MODEL_FILE="$MODEL_DIR/${RESOURCE_PASCAL}Model.php"

if [[ -f "$MODEL_FILE" && -z "$FORCE" ]]; then
    echo "→ Skipping model (already exists): $MODEL_FILE"
else
    cat > "$MODEL_FILE" << 'EOF'
<?php
declare(strict_types=1);

namespace App\Models;

use App\Db;

class ${RESOURCE_PASCAL}Model
{
    private Db $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    // Example: get all records
    public function findAll(int $limit = 100): array
    {
        return $this->db->find([
            'tbl'   => '${RESOURCE_LOWER}s',
            'order' => ['id' => 'desc'],
            'limit' => $limit,
        ]);
    }

    // Example: get one record
    public function findById(int $id): ?array
    {
        $results = $this->db->find([
            'tbl'   => '${RESOURCE_LOWER}s',
            'where' => ['id' => $id],
            'limit' => 1,
        ]);

        return $results[0] ?? null;
    }

    // Add your own methods: create(), update(), delete(), etc.
}
EOF
    echo "✓ Created model: $MODEL_FILE"
fi

# ────────────────────────────────────────────────────────────────
# 2. Controller
# ────────────────────────────────────────────────────────────────
CONTROLLER_FILE="$CONTROLLER_DIR/${RESOURCE_PASCAL}Controller.php"

if [[ -f "$CONTROLLER_FILE" && -z "$FORCE" ]]; then
    echo "→ Skipping controller (already exists): $CONTROLLER_FILE"
else
    cat > "$CONTROLLER_FILE" << 'EOF'
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\${RESOURCE_PASCAL}Model;
use App\Smarty;

class ${RESOURCE_PASCAL}Controller
{
    private ${RESOURCE_PASCAL}Model $model;

    public function __construct()
    {
        $this->model = new ${RESOURCE_PASCAL}Model();
    }

    public function index(): void
    {
        $items = $this->model->findAll();

        $smarty = new Smarty();

        $data = [
            'title' => '${RESOURCE_PASCAL} List',
            'items' => $items,
        ];

        $header = file_get_contents(dirname(__DIR__, 2) . '/views/layouts/header.html');
        $footer = file_get_contents(dirname(__DIR__, 2) . '/views/layouts/footer.html');
        $content = file_get_contents(dirname(__DIR__, 2) . '/views/${RESOURCE_LOWER}/index.html');

        $rendered = $smarty->render($content, $data);

        echo $header . $rendered . $footer;
    }

    // Add more actions: show(), create(), store(), edit(), update(), destroy()
}
EOF
    echo "✓ Created controller: $CONTROLLER_FILE"
fi

# ────────────────────────────────────────────────────────────────
# 3. Views (basic index view)
# ────────────────────────────────────────────────────────────────
VIEW_FILE="$VIEW_DIR/index.html"

if [[ -f "$VIEW_FILE" && -z "$FORCE" ]]; then
    echo "→ Skipping view (already exists): $VIEW_FILE"
else
    mkdir -p "$VIEW_DIR"
    cat > "$VIEW_FILE" << 'EOF'
<h1>${RESOURCE_PASCAL} List</h1>

{{#if items}}
<ul>
  {{#each items}}
  <li>ID {{{id}}} – {{{name}}} ({{{status}}})</li>
  {{/each}}
</ul>
{{else}}
<p>No ${RESOURCE_LOWER}s found.</p>
{{/if}}
EOF
    echo "✓ Created view: $VIEW_FILE"
fi

echo ""
echo "Done! You can now:"
echo "  • Add route in your router / FrontController"
echo "  • Visit: /php/${RESOURCE_LOWER}"
echo "  • Customize ${RESOURCE_PASCAL}Model, ${RESOURCE_PASCAL}Controller and views/${RESOURCE_LOWER}/index.html"
