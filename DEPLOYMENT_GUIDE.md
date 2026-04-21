# DEPLOYMENT CHECKLIST - homedelvalle.mx

Guía paso a paso para desplegar cambios en el servidor de producción.

## Pre-Deployment

### 1. Verificar estado local
```bash
git status  # Debe estar limpio
git log --oneline -5  # Ver últimos commits
composer show | grep -E "endroid|dompdf|intervention"  # Ver versiones
```

### 2. Revisar commits a desplegar
```bash
git log origin/main..main  # Commits no pusheados
git log --since="1 day" --oneline  # Cambios del último día
```

### 3. Si hay cambios en composer.json
```bash
git diff composer.json  # Revisar qué se cambió
composer install --dry-run  # Simular instalación
```

## Deployment

### En el servidor

```bash
cd /www/wwwroot/homedelvalle.mx

# 1. Descargar cambios
git pull origin main
# Si falla aquí, revisar git status en servidor

# 2. Si hay cambios en composer.json o composer.lock
composer install --no-dev --optimize-autoloader
# NUNCA usar --no-scripts en producción

# 3. Si hay migraciones nuevas
php artisan migrate --force
# El --force es necesario en producción

# 4. Limpiar caches
php artisan config:cache
php artisan route:cache
php artisan view:clear
php artisan cache:clear

# 5. Verificar symbolic link para storage
php artisan storage:link  # Idempotente, seguro ejecutar siempre

# 6. (Opcional) Si hay assets nuevos
npm install
npm run build
```

## Post-Deployment Testing

### Checklist Funcional

#### QR Code System
- [ ] Abrir admin → Properties → Ver una propiedad
- [ ] QR card aparece en sidebar
- [ ] Click "Generar QR"
  - [ ] Se genera sin errores
  - [ ] Preview aparece
  - [ ] Archivo existe en `storage/app/public/qr-codes/properties/{id}/qr.png`
- [ ] Click "Descargar PNG"
  - [ ] Se descarga archivo `propiedad-{id}-qr.png`
  - [ ] Archivo es válido (abrirlo)
- [ ] Click "Descargar SVG"
  - [ ] Se descarga archivo `propiedad-{id}-qr.svg`
  - [ ] Es XML válido
- [ ] Click "Regenerar QR"
  - [ ] Se regenera (mismo archivo, nuevo contenido)
  - [ ] Timestamp de "Generado:" se actualiza
- [ ] Click "Eliminar QR"
  - [ ] QR desaparece
  - [ ] Archivo se elimina de storage
  - [ ] Card vuelve a "No hay QR"

#### General
- [ ] Homepage carga sin errores
- [ ] Propiedades públicas cargan
- [ ] Gallery de fotos funciona
- [ ] Sin errores 500 en logs

### Verificación de Logs

```bash
tail -100 /www/wwwroot/homedelvalle.mx/storage/logs/laravel.log
# Debe estar vacío o con logs normales, sin ERROR
```

### Verificación de Permisos

```bash
ls -la /www/wwwroot/homedelvalle.mx/storage/app/public/qr-codes/
# Debe existir y ser accesible

stat /www/wwwroot/homedelvalle.mx/storage/app/public/
# Permisos: 755, owner: www-data:www-data (o equivalente)
```

## Rollback (Si algo falla)

### Opción 1: Git revert (Recomendado)
```bash
cd /www/wwwroot/homedelvalle.mx

# Ver último commit
git log --oneline -1

# Revertir si fue el último
git revert HEAD --no-edit
git push origin main

# En servidor
git pull origin main
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

### Opción 2: Git reset (Solo si nadie más hizo push)
```bash
git reset --hard origin/main~1  # Ir al commit anterior
git push --force origin main  # CUIDADO: fuerza el push

# Igual limpiar caches después
php artisan config:cache
php artisan view:clear
```

### Opción 3: Volver versión anterior de librería
```bash
composer require endroid/qr-code:^6.0 --no-update
# (O la versión que funcionaba)
composer install
php artisan config:cache
```

## Monitoreo Post-Deployment

### Primeras 24 horas
```bash
# Ejecutar cada hora
tail -50 /www/wwwroot/homedelvalle.mx/storage/logs/laravel.log | grep ERROR

# Verificar tráfico
# (Depende de tu monitoring tool)
```

### Alertas a activar
- [ ] Error 500 rate > 0.1%
- [ ] Response time > 2s
- [ ] QR generation failures
- [ ] Storage write errors

## Deployment Scenarios

### Escenario 1: Solo cambios en código PHP
```bash
git pull
php artisan config:cache
php artisan route:cache
php artisan view:clear
```
✅ Más rápido, menos riesgoso

### Escenario 2: Cambios en composer.json
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
php artisan cache:clear
```
⚠️ Puede tomar 2-5 minutos

### Escenario 3: Cambios en migrations
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan cache:clear
```
⚠️ Puede afectar datos, revisar migration primero

### Escenario 4: Cambios en assets (CSS/JS)
```bash
git pull
npm install
npm run build
php artisan config:cache
php artisan view:clear
```
✅ Solo afecta frontend

## Comandos Útiles

```bash
# Ver último deploy
git log --oneline -1

# Ver cambios desde último deploy
git log origin/main..main --oneline

# Ver cambios de una sola persona
git log --oneline --author="Nombre"

# Ver cambios en archivos específicos
git log --oneline -- app/Services/PropertyQrService.php

# Ver estado del storage
df -h /www/wwwroot/homedelvalle.mx/storage/

# Ver permisos del storage
find /www/wwwroot/homedelvalle.mx/storage -type f -exec ls -l {} \; | head -10

# Buscar QRs generados
find /www/wwwroot/homedelvalle.mx/storage/app/public/qr-codes -type f | wc -l

# Ver tamaño total de QRs
du -sh /www/wwwroot/homedelvalle.mx/storage/app/public/qr-codes/
```

## Troubleshooting Common Issues

### Git pull fails: "Your local changes would be overwritten"
```bash
git status  # Ver qué está uncommitted
git stash  # Guardar cambios locales
git pull
git stash pop  # Restaurar si es necesario
```

### Composer install hangs
```bash
# Ctrl+C para cancelar
composer install --no-dev --no-interaction --prefer-dist
```

### Permission denied on storage
```bash
# Revisar usuario actual
whoami
id

# Fijar permisos
sudo chown -R www-data:www-data /www/wwwroot/homedelvalle.mx/storage
sudo chmod -R 755 /www/wwwroot/homedelvalle.mx/storage
```

### QR generation fails post-deploy
```bash
# Verificar versión de endroid
composer show endroid/qr-code

# Ver error específico
tail -50 storage/logs/laravel.log | grep -A 10 "ERROR"

# Si es problema de API:
# - Revisar QR_IMPLEMENTATION.md
# - Revisar CRITICAL_VERSIONS.md
# - Revertir último deploy
```

### Database migration fails
```bash
# Ver qué migraciones están pendientes
php artisan migrate:status

# Rollback última migración
php artisan migrate:rollback

# Re-run migraciones con más verbose
php artisan migrate:refresh --step=1 -v
```

## Communication Template

Cuando despliegues un cambio importante:

```
🚀 DEPLOYMENT NOTICE

Cambio: [Descripción corta]
Fecha: [Fecha/Hora]
Duración: [Tiempo estimado de downtime si aplica]
Impacto: [Qué se afecta]

Si hay problemas:
- Contactar a: [Tu nombre]
- Status page: [URL]
- Rollback: Disponible en 5 minutos

Cambios principales:
- [Cambio 1]
- [Cambio 2]
```

## Documentación Relacionada

- QR_IMPLEMENTATION.md - Detalles técnicos del sistema QR
- CRITICAL_VERSIONS.md - Versiones de librerías críticas
- migrations/ - Esquema de BD
