#!/bin/bash
# ============================================
# Script de deploy para cPanel
# Ejecutar en Terminal de cPanel:
#   cd /home/homed0b1/repositories/homedelvalle
#   bash cpanel-deploy.sh
# ============================================

REPO="/home/homed0b1/repositories/homedelvalle"
PUBLIC_HTML="/home/homed0b1/public_html"

echo "=== Deploy Home del Valle CRM ==="

# 1. Crear index.php para cPanel
cat > "$PUBLIC_HTML/index.php" << 'PHPEOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = '/home/homed0b1/repositories/homedelvalle';

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

// Decirle a Laravel que public_html es la carpeta publica
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
PHPEOF
echo "[OK] index.php"

# 2. Copiar .htaccess
cp "$REPO/public/.htaccess" "$PUBLIC_HTML/.htaccess"
echo "[OK] .htaccess"

# 3. Copiar archivos estaticos
cp "$REPO/public/favicon.ico" "$PUBLIC_HTML/" 2>/dev/null
cp "$REPO/public/robots.txt" "$PUBLIC_HTML/" 2>/dev/null
echo "[OK] favicon, robots"

# 4. Copiar build (CSS/JS) - usar copia, no symlink
rm -rf "$PUBLIC_HTML/build"
cp -r "$REPO/public/build" "$PUBLIC_HTML/build"
echo "[OK] build/ copiado (CSS/JS)"

# 5. Storage link
rm -rf "$PUBLIC_HTML/storage"
ln -sf "$REPO/storage/app/public" "$PUBLIC_HTML/storage" 2>/dev/null
# Si symlink falla, copiar
if [ ! -L "$PUBLIC_HTML/storage" ]; then
    mkdir -p "$PUBLIC_HTML/storage"
    cp -r "$REPO/storage/app/public/"* "$PUBLIC_HTML/storage/" 2>/dev/null
fi
echo "[OK] storage/"

# 6. Vendor assets (tinymce, etc)
if [ -d "$REPO/public/vendor" ]; then
    rm -rf "$PUBLIC_HTML/vendor"
    cp -r "$REPO/public/vendor" "$PUBLIC_HTML/vendor"
    echo "[OK] vendor/"
fi

# 7. Permisos
chmod -R 775 "$REPO/storage" 2>/dev/null
chmod -R 775 "$REPO/bootstrap/cache" 2>/dev/null
echo "[OK] permisos"

# 8. Composer install (si no existe vendor)
if [ ! -d "$REPO/vendor" ]; then
    cd "$REPO"
    composer install --no-dev --optimize-autoloader 2>&1
    echo "[OK] composer install"
fi

# 9. Limpiar cache
cd "$REPO"
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan view:clear 2>/dev/null
php artisan route:clear 2>/dev/null
echo "[OK] cache limpiado"

# 10. Migraciones
php artisan migrate --force 2>&1
echo "[OK] migraciones"

# 11. Verificar
echo ""
echo "=== Verificacion ==="
echo "PHP: $(php -v | head -1)"
echo "Laravel: $(php artisan --version)"
echo "Build CSS: $(ls -la $PUBLIC_HTML/build/assets/*.css 2>/dev/null | wc -l) archivos"
echo "Build JS: $(ls -la $PUBLIC_HTML/build/assets/*.js 2>/dev/null | wc -l) archivos"
echo ""
echo "=== Deploy completado ==="
echo "Visita https://homedelvalle.mx para verificar"
