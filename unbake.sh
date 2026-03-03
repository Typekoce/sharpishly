#!/usr/bin/env bash
# unbake.sh - Remove Model, Controller and Views created by bake.sh
# Usage: ./unbake.sh user
#        ./unbake.sh user --force

set -euo pipefail

RESOURCE="${1:-}"
FORCE=false

if [[ -z "$RESOURCE" ]]; then
    echo "Usage: $0 <resource_name> [--force]"
    echo "Example: ./unbake.sh user"
    exit 1
fi

if [[ "${2:-}" == "--force" ]]; then
    FORCE=true
fi

# Naming conventions
RESOURCE_LOWER=$(echo "$RESOURCE" | tr '[:upper:]' '[:lower:]')
RESOURCE_PASCAL=$(echo "$RESOURCE" | awk '{print toupper(substr($0,1,1)) tolower(substr($0,2))}')

# Paths (matching your bake.sh structure)
MODEL_FILE="php/src/Models/${RESOURCE_PASCAL}Model.php"
CONTROLLER_FILE="php/src/Controllers/${RESOURCE_PASCAL}Controller.php"
VIEW_DIR="php/src/views/${RESOURCE_LOWER}"

echo "⚠️  About to delete the following:"
echo "   • $MODEL_FILE"
echo "   • $CONTROLLER_FILE"
echo "   • $VIEW_DIR/ (entire folder)"
echo ""

if [[ "$FORCE" != true ]]; then
    read -p "Type 'yes' to confirm deletion: " confirm
    if [[ "$confirm" != "yes" ]]; then
        echo "Aborted."
        exit 0
    fi
fi

rm -f "$MODEL_FILE" 2>/dev/null || true
rm -f "$CONTROLLER_FILE" 2>/dev/null || true
rm -rf "$VIEW_DIR" 2>/dev/null || true

echo "✅ Unbake complete for '$RESOURCE'"
echo "   Removed Model, Controller, and views/${RESOURCE_LOWER}/"
