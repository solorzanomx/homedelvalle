#!/bin/bash
# ============================================
# Script de deploy para cPanel
# Ejecutar desde Terminal de cPanel:
# bash /home/homed0b1/repositories/homedelvalle/cpanel-deploy.sh
# ============================================

REPO="/home/homed0b1/repositories/homedelvalle"
PUBLIC_HTML="/home/homed0b1/public_html"

echo "=== Deploy Home del Valle CRM ==="

# 1. Copiar index.php modificado para cPanel
cat > "$PUBLIC_HTML/index.php" << 'PHPEOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Path al repositorio
$basePath = '/home/homed0b1/repositories/homedelvalle';

// Maintenance mode
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader
require $basePath.'/vendor/autoload.php';

// Bootstrap Laravel
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

// Decirle a Laravel que public_html es la carpeta publica
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
PHPEOF
echo "✓ index.php creado"

# 2. Copiar .htaccess de Laravel
cp "$REPO/public/.htaccess" "$PUBLIC_HTML/.htaccess"
echo "✓ .htaccess copiado"

# 3. Copiar favicon y robots
cp "$REPO/public/favicon.ico" "$PUBLIC_HTML/favicon.ico" 2>/dev/null
cp "$REPO/public/robots.txt" "$PUBLIC_HTML/robots.txt" 2>/dev/null
echo "✓ favicon y robots copiados"

# 4. Crear symlinks para assets
# Build (CSS/JS compilados)
rm -rf "$PUBLIC_HTML/build"
ln -sf "$REPO/public/build" "$PUBLIC_HTML/build"
echo "✓ Symlink build/ creado"

# Storage (fotos, uploads)
rm -rf "$PUBLIC_HTML/storage"
ln -sf "$REPO/storage/app/public" "$PUBLIC_HTML/storage"
echo "✓ Symlink storage/ creado"

# Vendor (tinymce, etc)
if [ -d "$REPO/public/vendor" ]; then
    rm -rf "$PUBLIC_HTML/vendor"
    ln -sf "$REPO/public/vendor" "$PUBLIC_HTML/vendor"
    echo "✓ Symlink vendor/ creado"
fi

# 5. Permisos
chmod -R 775 "$REPO/storage" 2>/dev/null
chmod -R 775 "$REPO/bootstrap/cache" 2>/dev/null
echo "✓ Permisos ajustados"

# 6. Limpiar cache
cd "$REPO"
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan view:clear 2>/dev/null
php artisan route:clear 2>/dev/null
echo "✓ Cache limpiado"

# 7. Migrar base de datos
php artisan migrate --force 2>/dev/null
echo "✓ Migraciones ejecutadas"

echo ""
echo "=== Deploy completado ==="
echo "Verifica en tu navegador que el sitio cargue correctamente."
