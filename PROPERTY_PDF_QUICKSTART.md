# ⚡ QUICK START - 5 MINUTOS PARA TENER FUNCIONAL

## Paso 1: Verificar Dependencias (2 min)

```bash
# En tu terminal, en la raíz del proyecto Laravel
composer require barryvdh/laravel-dompdf
composer require simplesoftware/simple-qr-code
```

## Paso 2: Copiar Archivos (1 min)

```bash
# Ya deberían estar en tu proyecto:
# - resources/views/pdf/property-sheet.blade.php
# - app/Http/Controllers/PDF/PropertySheetController.php
# - app/Console/Commands/GeneratePropertyQrCodes.php

# Si no, copiarlos manualmente a esas ubicaciones
```

## Paso 3: Agregar Rutas (1 min)

En `routes/web.php`, añade:

```php
Route::get('/properties/{property:slug}/pdf', 
    [\App\Http\Controllers\PDF\PropertySheetController::class, 'downloadPropertySheet']
)->name('properties.pdf.download');

Route::get('/properties/{property:slug}/pdf/preview', 
    [\App\Http\Controllers\PDF\PropertySheetController::class, 'previewPropertySheet']
)->name('properties.pdf.preview');
```

## Paso 4: Probar (1 min)

```bash
# En el navegador, ir a:
http://localhost:8000/properties/mi-propiedad-slug/pdf

# Debería descargar un PDF listo para usar

# Para previsualizar:
http://localhost:8000/properties/mi-propiedad-slug/pdf/preview
```

---

## 🎯 Eso es Todo

El PDF ya funciona con:
✅ Tus propiedades existentes
✅ Modo con y sin broker
✅ Imágenes automáticas
✅ QR integrado
✅ Diseño premium

---

## Próximos Pasos (Opcional)

### Generar QR para todas las propiedades

```bash
php artisan properties:generate-qr-codes
```

### Personalizar colores

Editar `resources/views/pdf/property-sheet.blade.php`, buscar `:root {`

### Generar QR para una propiedad específica

```bash
php artisan properties:generate-qr-codes --property-id=5
```

---

## Ejemplos de URLs

```
# Sin broker (público)
/properties/casa-lujo-001/pdf

# Con broker actual (requiere login)
/properties/casa-lujo-001/pdf?include_broker=1

# Preview en navegador
/properties/casa-lujo-001/pdf/preview
```

---

## Troubleshooting Rápido

**"Error 404"**
→ Asegúrate de que la propiedad existe y tiene un slug válido

**"Error 500"**
→ Verificar logs: `storage/logs/laravel.log`

**"Imágenes no aparecen"**
→ Las imágenes deben estar en `storage/app/public/...`

**"No funciona con broker"**
→ Usuario debe estar logueado o usar `include_broker=0`

---

## Personalización Mínima

### Cambiar logo
Reemplazar: `public/images/logo-homedelvalle.png`

### Cambiar contacto
En `property-sheet.blade.php`, buscar:
- `+52 55 1234 5678` → Tu teléfono
- `info@homedelvalle.mx` → Tu email

### Cambiar colores primarios
En `<style>` de la plantilla:
```css
--primary-dark: #1a3a52;    /* Tu color */
--primary-light: #0066cc;   /* Tu acento */
```

---

## 📚 Leer después

1. **PROPERTY_PDF_SUMMARY.md** - Resumen completo
2. **PROPERTY_PDF_GUIDE.md** - Guía detallada
3. **PROPERTY_PDF_DESIGN_GUIDE.md** - Guía de diseño

---

## ✨ Ya está

¡Felicidades! Tienes fichas técnicas PDF premium funcionando.

El resto es customización y mejoras opcionales.

