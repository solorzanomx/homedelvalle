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

# 0. ELIMINAR index.html (Apache lo sirve ANTES que index.php)
if [ -f "$PUBLIC_HTML/index.html" ]; then
    rm -f "$PUBLIC_HTML/index.html"
    echo "[OK] index.html eliminado (bloqueaba Laravel)"
fi

# 1. Crear index.php para cPanel
cat > "$PUBLIC_HTML/index.php" << 'PHPEOF'
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = '/home/homed0b1/repositories/homedelvalle';

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

$app = require_once $basePath.'/bootstrap/app.php';

// Decirle a Laravel que public_html es la carpeta publica
$app->usePublicPath('/home/homed0b1/public_html');

$app->handleRequest(Request::capture());
PHPEOF
echo "[OK] index.php creado"

# 2. Copiar .htaccess con DirectoryIndex
cat > "$PUBLIC_HTML/.htaccess" << 'HTEOF'
DirectoryIndex index.php

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTEOF
echo "[OK] .htaccess con DirectoryIndex"

# 3. Copiar archivos estaticos
cp "$REPO/public/favicon.ico" "$PUBLIC_HTML/" 2>/dev/null
cp "$REPO/public/robots.txt" "$PUBLIC_HTML/" 2>/dev/null
echo "[OK] favicon, robots"

# 4. Copiar build (CSS/JS compilados)
rm -rf "$PUBLIC_HTML/build"
if [ -d "$REPO/public/build" ]; then
    cp -r "$REPO/public/build" "$PUBLIC_HTML/build"
    echo "[OK] build/ copiado (CSS/JS)"
else
    echo "[WARN] No existe public/build/ en el repo"
fi

# 5. Storage link
rm -rf "$PUBLIC_HTML/storage"
ln -sf "$REPO/storage/app/public" "$PUBLIC_HTML/storage" 2>/dev/null
if [ ! -L "$PUBLIC_HTML/storage" ]; then
    mkdir -p "$PUBLIC_HTML/storage"
    cp -r "$REPO/storage/app/public/"* "$PUBLIC_HTML/storage/" 2>/dev/null
fi
echo "[OK] storage/"

# 6. Vendor assets publicos
if [ -d "$REPO/public/vendor" ]; then
    rm -rf "$PUBLIC_HTML/vendor"
    cp -r "$REPO/public/vendor" "$PUBLIC_HTML/vendor"
    echo "[OK] vendor/ publico"
fi

# 7. Permisos
chmod -R 775 "$REPO/storage" 2>/dev/null
chmod -R 775 "$REPO/bootstrap/cache" 2>/dev/null
mkdir -p "$REPO/storage/framework/sessions"
mkdir -p "$REPO/storage/framework/views"
mkdir -p "$REPO/storage/framework/cache"
mkdir -p "$REPO/storage/logs"
echo "[OK] permisos y directorios"

# 8. Composer install
cd "$REPO"
if [ ! -d "$REPO/vendor" ] || [ "$1" == "--full" ]; then
    composer install --no-dev --optimize-autoloader 2>&1
    echo "[OK] composer install"
else
    echo "[SKIP] vendor/ existe (usa --full para reinstalar)"
fi

# 9. Verificar .env
if [ ! -f "$REPO/.env" ]; then
    echo "[ERROR] No existe .env - crea uno con los datos de tu DB"
    echo "  cp $REPO/.env.example $REPO/.env"
    echo "  php artisan key:generate"
fi

# 10. Limpiar cache
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan view:clear 2>/dev/null
php artisan route:clear 2>/dev/null
echo "[OK] cache limpiado"

# 11. Migraciones
php artisan migrate --force 2>&1
echo "[OK] migraciones"

# 12. Seeders (solo la primera vez)
if [ "$1" == "--seed" ]; then
    php artisan db:seed --class=HelpCenterSeeder --force 2>&1
    php artisan db:seed --class=MarketingAutomationSeeder --force 2>&1
    echo "[OK] seeders ejecutados"
fi

# 13. Verificacion final
echo ""
echo "=== VERIFICACION ==="
echo "PHP: $(php -v 2>/dev/null | head -1)"
echo "Laravel: $(php artisan --version 2>/dev/null)"
echo ""
echo "Archivos en public_html:"
echo "  index.php: $([ -f $PUBLIC_HTML/index.php ] && echo 'SI' || echo 'NO')"
echo "  index.html: $([ -f $PUBLIC_HTML/index.html ] && echo 'SI (PROBLEMA!)' || echo 'NO (correcto)')"
echo "  .htaccess: $([ -f $PUBLIC_HTML/.htaccess ] && echo 'SI' || echo 'NO')"
echo "  build/: $([ -d $PUBLIC_HTML/build ] && echo 'SI' || echo 'NO')"
echo "  CSS: $(ls $PUBLIC_HTML/build/assets/*.css 2>/dev/null | wc -l | tr -d ' ') archivos"
echo "  JS: $(ls $PUBLIC_HTML/build/assets/*.js 2>/dev/null | wc -l | tr -d ' ') archivos"
echo ""
echo "=== Deploy completado ==="
echo "Visita https://homedelvalle.mx para verificar"
