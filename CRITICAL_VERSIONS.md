# VERSIONES CRÍTICAS - homedelvalle.mx

Archivo de referencia de versiones de librerías críticas para evitar problemas futuros.

## PHP y Laravel

```
PHP: ^8.2 (mínimo 8.2, máximo antes de 9.0)
Laravel: ^11.0
```

## Librerías de Generación de Contenido

### QR Codes
```
endroid/qr-code: ^6.0
- Versión instalada: 6.0.9
- NO usar versiones 4.x o 5.x (API completamente diferente)
- Métodos correctos en v6:
  * new QrCode($url)
  * $writer->write($qrCode)
  * $result->getString()
- Métodos que NO existen en v6:
  * QrCode::create() ✗
  * setSize() ✗
  * setEncoding() ✗
  * getStream() ✗
```

### PDF
```
dompdf/dompdf: ^3.0
- Versión instalada: 3.x
- Uso: PropertyFichaController para fichas técnicas
```

### Imágenes
```
intervention/image: ^3.0
- Versión instalada: 3.x
- Uso: ImageOptimizer para optimización de fotos
- Cambio importante de v2 a v3: API renovada
```

## Dependencias Indirectas Importantes

```
bacon/bacon-qr-code: ^3.1
- Depende de: endroid/qr-code
- Driver QR subyacente
```

## Laravel Core

```
laravel/framework: ^11.0
- Versión LTS actual
- Migrate, Storage, Blade, etc.
```

## Pasos para Actualizar Librerías

1. **Antes de actualizar:**
   ```bash
   git status  # Asegurar rama limpia
   composer show  # Listar versiones actuales
   ```

2. **Actualizar una librería específica:**
   ```bash
   composer update endroid/qr-code --no-dev
   ```

3. **Verificar cambios:**
   ```bash
   composer show | grep endroid
   git diff composer.json composer.lock
   ```

4. **Testear:**
   - Generar QR en admin
   - Descargar PNG y SVG
   - Verificar logs

5. **Si falla:**
   ```bash
   git checkout composer.json composer.lock
   composer install
   ```

## Historial de Problemas por Versiones

### Problema: endroid/qr-code v4.x vs v6.x
**Fecha:** 2026-04-21
**Síntoma:** Error 500 al generar QR
**Causa:** API completamente diferente entre versiones
**Solución:** Reescribir PropertyQrService para v6

| Método | v4.x | v5.x | v6.x |
|--------|------|------|------|
| Crear QR | `QrCode::create()` | `QrCode::create()` | `new QrCode()` |
| Tamaño | `.setSize()` | `.setSize()` | (configurado internamente) |
| Encoding | `.setEncoding()` | `.setEncoding()` | (automático) |
| Obtener contenido | `.writeString()` | `.writeString()` | `.getString()` |

### Regla General
**Antes de actualizar una librería que aparece en PropertyQrService:**
1. Revisar CHANGELOG de la nueva versión
2. Buscar cambios en la API
3. Testear generación de QR antes de deploy

## Cómo Verificar Compatibilidad

```bash
# Ver qué packages usan endroid/qr-code
composer depends endroid/qr-code

# Ver changelog
composer show --all --format=json | jq '.[] | select(.name=="endroid/qr-code")'
```

## Monitoring de Cambios

Para evitar problemas en el futuro:

1. **Revisión semanal:**
   ```bash
   composer outdated  # Ver qué está desactualizado
   ```

2. **Antes de cualquier deploy:**
   ```bash
   composer install --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:clear
   ```

3. **Testing post-deploy:**
   - [ ] QR generation works
   - [ ] QR download works (PNG y SVG)
   - [ ] QR regeneration works
   - [ ] Admin page loads without errors

## Documentación Oficial

- Laravel 11: https://laravel.com/docs/11
- endroid/qr-code v6: https://github.com/endroid/qr-code
- dompdf: https://github.com/dompdf/dompdf
- intervention/image: https://image.intervention.io

## Emergency Rollback

Si algo falla después de composer update:

```bash
# Revertir último cambio en dependencias
git checkout composer.lock
composer install

# O revertir completamente
git revert HEAD
git checkout composer.lock
composer install
```

## Notas para Futuros Desarrolladores

- **No actualices endroid/qr-code sin revisar PropertyQrService primero**
- **PHP 9.0 puede romper todo - testear antes**
- **Laravel 12+ puede tener cambios en Storage o Blade**
- **Mantén un backup de composer.lock antes de actualizar**
