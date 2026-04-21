# QR Code System Implementation

## Resumen

Sistema completo de generación, almacenamiento y reutilización de códigos QR para propiedades en homedelvalle.mx. Los QR se generan una sola vez y se reutilizan, regenerándose solo si la URL cambia o se solicita manualmente.

## Dependencias Críticas

### Versión instalada
```json
"endroid/qr-code": "^6.0"
```

**IMPORTANTE:** La versión 6.0.9 tiene una API diferente a versiones anteriores. No usar métodos de versiones 4.x o 5.x.

### API Correcta (v6.0.9)
```php
// ✅ CORRECTO - Usar constructor
$qrCode = new QrCode($url);
$writer = new PngWriter();
$result = $writer->write($qrCode);
$content = $result->getString();  // ← Método correcto

// ❌ INCORRECTO - Estos NO existen en v6
$qrCode::create($url);  // No existe
$qrCode->setSize(300);  // No existe
$qrCode->setEncoding('UTF-8');  // No existe
$result->getStream();  // No existe
```

## Estructura de Archivos

```
database/migrations/
  └── 2026_04_21_021230_create_property_qr_codes_table.php

app/Models/
  └── PropertyQrCode.php

app/Services/
  └── PropertyQrService.php

app/Http/Controllers/Admin/
  └── PropertyQrController.php

resources/views/admin/properties/partials/
  └── qr-card.blade.php

routes/web.php (actualizado con rutas QR)
```

## Base de Datos

### Tabla: property_qr_codes
```sql
CREATE TABLE property_qr_codes (
  id BIGINT PRIMARY KEY,
  property_id BIGINT NOT NULL UNIQUE,
  qr_code_path VARCHAR(255) NULLABLE,
  qr_url VARCHAR(500) NOT NULL,
  generated_at TIMESTAMP NULLABLE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);
```

**Campos:**
- `property_id`: FK a properties, índice único (un QR por propiedad)
- `qr_code_path`: Ruta relativa al archivo PNG guardado
- `qr_url`: URL que se codificó en el QR (para detectar cambios)
- `generated_at`: Timestamp de generación (para auditoría)
- Cascade delete: Al borrar propiedad, se elimina el QR automáticamente

## Almacenamiento

```
storage/app/public/qr-codes/
  └── properties/
      └── {property_id}/
          └── qr.png
```

**Acceso web:**
```
https://homedelvalle.mx/storage/qr-codes/properties/{property_id}/qr.png
```

## Servicio: PropertyQrService

### Métodos principales

#### `generateOrReuse(Property $property, bool $forceRegenerate = false)`
- Genera QR si no existe
- Si existe, verifica si URL cambió
- Si URL no cambió: reutiliza el existente
- Si URL cambió: regenera automáticamente
- Si `$forceRegenerate = true`: siempre regenera

```php
$qrService = app(PropertyQrService::class);
$qrCode = $qrService->generateOrReuse($property);
```

#### `generate(Property $property, ?string $url = null)`
- Genera nuevo QR (sobrescribe el anterior si existe)
- Crea directorio automáticamente si no existe
- Retorna objeto PropertyQrCode

#### `getPublicUrl(PropertyQrCode $qrCode)`
- Retorna URL pública del QR (con Storage::url)
- Usable en vistas: `{{ $qrService->getPublicUrl($qrCode) }}`

#### `getAsSvg(PropertyQrCode $qrCode)`
- Retorna QR como SVG string
- Útil para impresión y escalado vectorial
- Perfecto para lonas y materiales grandes

#### `regenerate(Property $property)`
- Elimina archivo anterior
- Genera nuevo QR
- Retorna PropertyQrCode

#### `delete(Property $property)`
- Elimina archivo PNG
- Elimina registro BD
- Retorna bool

## Controlador: PropertyQrController

### Rutas
```
POST   /properties/{property}/qr/generate
GET    /properties/{property}/qr/download?format=png|svg
DELETE /properties/{property}/qr
```

### Acciones

#### generate()
```php
POST /properties/4/qr/generate
POST /properties/4/qr/generate?force=1  // Fuerza regeneración
```
Respuesta: Redirect back con mensaje 'success' o 'error'

#### download()
```php
GET /properties/4/qr/download?format=png
GET /properties/4/qr/download?format=svg

// Descargas como archivo con nombre: propiedad-{id}-qr.png|svg
```

#### delete()
```php
DELETE /properties/4/qr

// Respuesta: Redirect back con confirmación
```

## Vista: qr-card.blade.php

Ubicación: `resources/views/admin/properties/partials/qr-card.blade.php`

Incluido en: `resources/views/properties/show.blade.php`

**Características:**
- Preview del QR (180x180px)
- Información: URL codificada, fecha generación
- Botón: Regenerar QR (con fuerza=1)
- Botones: Descargar PNG, Descargar SVG
- Botón: Eliminar QR
- Estado: Si no existe → botón Generar

**Inclusión:**
```blade
@include('admin.properties.partials.qr-card', ['property' => $property])
```

## Flujo de Uso

### 1️⃣ Primer acceso a propiedad
- Admin abre property show
- Card QR aparece vacío
- Click "Generar QR"
- Sistema genera y guarda en storage

### 2️⃣ Siguientes accesos (sin cambios)
- Sistema detecta que QR existe
- Verifica que URL sea la misma
- Reutiliza el mismo archivo (no regenera)

### 3️⃣ URL cambió (ej: easybroker_public_url)
- Sistema genera nuevo QR automáticamente
- Elimina archivo anterior
- Guarda nuevo

### 4️⃣ Regeneración manual
- Usuario click "Regenerar QR"
- Sistema fuerza regeneración con `force=1`
- Elimina anterior, crea nuevo

### 5️⃣ Descarga
- PNG: Raster, comprimido, estándar
- SVG: Vector, escalable, para impresión grande

### 6️⃣ Borrar propiedad
- Trigger: cascade delete en FK
- Elimina automáticamente:
  - Archivo PNG
  - Registro en BD

## Integración con PDF

Para incluir QR en ficha técnica (PropertyFichaController):

```php
// En el método pdf()
$qrService = app(PropertyQrService::class);
$qrCode = $qrService->generateOrReuse($property);

// En la vista PDF
@if($qrCode && $qrCode->qr_code_path)
    <img src="{{ asset('storage/' . $qrCode->qr_code_path) }}" 
         width="200" height="200" alt="QR Code">
@endif
```

## Troubleshooting

### Error: "Call to undefined method create()"
**Causa:** Código escrito para versión 4.x o 5.x
**Solución:** Usar constructor: `new QrCode($url)`

### Error: "Call to undefined method setEncoding()"
**Causa:** Método no existe en v6
**Solución:** Remover ese método, no es necesario

### Error: "getStream() not found"
**Causa:** Método antiguo
**Solución:** Usar `$result->getString()`

### QR no se regenera al cambiar URL
**Causa:** `generateOrReuse()` sin `force=true`
**Verificar:**
1. ¿Cambió `property->easybroker_public_url`?
2. ¿Se ejecutó el formulario con fuerza=1?
3. ¿La URL en BD es diferente a la nueva?

### Permisos de almacenamiento
```bash
# Si hay error al guardar QR:
chmod -R 755 storage/app/public
chown -R www-data:www-data storage/app/public
```

## Deployment

### Local
```bash
composer require endroid/qr-code  # Ya hecho
php artisan migrate
php artisan storage:link
```

### Servidor
```bash
cd /www/wwwroot/homedelvalle.mx
git pull
composer install
php artisan migrate
php artisan storage:link
php artisan view:clear
php artisan cache:clear
```

## Checklist Post-Deploy

- [ ] Git pull completado
- [ ] Composer install ejecutado
- [ ] Migración ejecutada
- [ ] Storage link existe
- [ ] Permisos correctos en `storage/app/public`
- [ ] Intentar generar QR en propiedad
- [ ] Verificar archivo en `storage/app/public/qr-codes/properties/{id}/qr.png`
- [ ] Descargar PNG y SVG funcionan
- [ ] URL en card es correcta
- [ ] Regenerar funciona
- [ ] Eliminar funciona

## Notas de Seguridad

- QR se accede via Storage::url (protegido por .htaccess si es necesario)
- Rutas protegidas por middleware 'auth'
- Solo usuarios autenticados pueden generar/descargar/eliminar
- Cascade delete previene huérfanos en BD

## Posibles Mejoras Futuras

1. **Historial de QR**: Guardar versiones anteriores
2. **Estilos de QR**: Color, logo en el centro
3. **Batch generation**: Generar múltiples QR a la vez
4. **QR en email**: Incluir automáticamente en fichas enviadas
5. **Analytics**: Trackear cuántos clicks tiene cada QR
6. **Expiración**: QR con fecha de expiración

## Referencias

- Documentación endroid/qr-code: https://github.com/endroid/qr-code
- Versión actual: 6.0.9 (compatible con PHP 8.1+)
