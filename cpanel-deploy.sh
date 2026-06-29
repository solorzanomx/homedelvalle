#!/bin/bash
# ============================================
# Script de deploy para el servidor VPS actual
# Ruta del proyecto: /www/wwwroot/homedelvalle.mx
#
# Uso:
#   cd /www/wwwroot/homedelvalle.mx
#   git pull && bash cpanel-deploy.sh
#
# Con reinstalación de composer:
#   bash cpanel-deploy.sh --full
#
# Con seeders:
#   bash cpanel-deploy.sh --seed
# ============================================

set -e

REPO="/www/wwwroot/homedelvalle.mx"

echo "=== Deploy Home del Valle CRM ==="
echo "Repo: $REPO"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "$REPO/artisan" ]; then
    echo "[ERROR] No se encontró artisan en $REPO — verifica la ruta del proyecto"
    exit 1
fi

cd "$REPO"

# 1. Permisos y directorios de storage
echo "[1/7] Permisos y directorios..."
chmod -R 775 "$REPO/storage" 2>/dev/null || true
chmod -R 775 "$REPO/bootstrap/cache" 2>/dev/null || true
mkdir -p "$REPO/storage/framework/sessions"
mkdir -p "$REPO/storage/framework/views"
mkdir -p "$REPO/storage/framework/cache"
mkdir -p "$REPO/storage/logs"
mkdir -p "$REPO/storage/app/public/avatars"
mkdir -p "$REPO/storage/app/presentations"
chmod 775 "$REPO/storage/app/presentations" 2>/dev/null || true
echo "[OK] Permisos y directorios"

# 2. Composer install
echo "[2/7] Composer..."
if [ ! -d "$REPO/vendor" ] || [ "$1" == "--full" ]; then
    composer install --no-dev --optimize-autoloader 2>&1
    echo "[OK] composer install completo"
else
    echo "[SKIP] vendor/ existe (usa --full para reinstalar)"
fi

# 3. Verificar .env
echo "[3/7] Variables de entorno..."
if [ ! -f "$REPO/.env" ]; then
    echo "[ERROR] No existe .env"
    echo "  cp $REPO/.env.example $REPO/.env"
    echo "  php artisan key:generate"
    exit 1
fi
echo "[OK] .env existe"

# 4. Limpiar y re-cachear
echo "[4/7] Cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "[OK] Cache limpiado"

# 5. Migraciones
echo "[5/7] Migraciones..."
php artisan migrate --force
echo "[OK] Migraciones aplicadas"

# 6. Seeders
echo "[6/7] Seeders..."
if [ "$1" == "--seed" ]; then
    php artisan db:seed --class=HelpCenterSeeder --force 2>&1
    php artisan db:seed --class=MarketingAutomationSeeder --force 2>&1
    echo "[OK] Seeders generales ejecutados"
fi

# Seeders idempotentes de presentación (seguros en cada deploy)
php artisan db:seed --class=PresentationTemplatesSeeder --force 2>&1 || echo "[WARN] PresentationTemplatesSeeder no existe o falló"
php artisan db:seed --class=PresentationEmailTemplateSeeder --force 2>&1 || echo "[WARN] PresentationEmailTemplateSeeder no existe o falló"
echo "[OK] Seeders de presentación"

# 7. Verificación Browsershot
echo "[7/7] Verificando Browsershot..."
NODE_BIN=$(php artisan tinker --execute="echo config('browsershot.node_path');" 2>/dev/null | tail -1)
CHROME_BIN=$(php artisan tinker --execute="echo config('browsershot.chrome_path');" 2>/dev/null | tail -1)
[ -f "$NODE_BIN" ] && echo "  [OK] Node: $NODE_BIN" || echo "  [WARN] Node no encontrado en '$NODE_BIN' — revisa BROWSERSHOT_NODE_PATH en .env"
[ -f "$CHROME_BIN" ] && echo "  [OK] Chrome: $CHROME_BIN" || echo "  [WARN] Chrome no encontrado en '$CHROME_BIN' — revisa BROWSERSHOT_CHROME_PATH en .env"

# Verificación final
echo ""
echo "=== VERIFICACIÓN ==="
echo "PHP:     $(php -v 2>/dev/null | head -1)"
echo "Laravel: $(php artisan --version 2>/dev/null)"
echo "Artisan: $([ -f $REPO/artisan ] && echo 'OK' || echo 'NO ENCONTRADO')"
echo "Build:   $([ -d $REPO/public/build ] && echo "OK ($(ls $REPO/public/build/assets/ 2>/dev/null | wc -l | tr -d ' ') archivos en assets/)" || echo 'NO — ejecuta npm run build localmente y commitea')"
echo ".env:    $([ -f $REPO/.env ] && echo 'OK' || echo 'FALTA')"
echo ""
echo "=== Deploy completado ==="
echo "Visita https://homedelvalle.mx para verificar"
