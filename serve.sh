#!/usr/bin/env bash
# Starts the Laravel dev server with:
#   - Unlimited execution time (avoids timeout on AI/Perplexity requests)
#   - Custom router so symlinked /storage files are served correctly
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROUTER=/tmp/laravel_router.php

# Write the router if it doesn't exist or is outdated
cat > "$ROUTER" <<'PHP'
<?php
$publicPath = $_SERVER['DOCUMENT_ROOT'];
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}
require_once $publicPath . '/index.php';
PHP

echo "Starting Home del Valle dev server at http://127.0.0.1:8000"
echo "Press Ctrl+C to stop."
echo ""

php -d max_execution_time=0 \
    -S 127.0.0.1:8000 \
    -t "$SCRIPT_DIR/public" \
    "$ROUTER"
