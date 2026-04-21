# Guía de Integración: Ficha Técnica PDF Premium

## 📋 Descripción General

Plantilla de PDF corporativo y editorial para fichas técnicas de propiedades en HomeDelValle. Diseño premium, minimalista y sofisticado. Soporta generación con y sin datos del broker.

---

## 🎨 Características de Diseño

### Paleta de Colores Corporativa
```
Azul Marino Profundo:    #1a3a52  (primario oscuro)
Azul Eléctrico:          #0066cc  (acento y énfasis)
Blanco Cálido:           #f8f9fa  (fondos neutros)
Gris Claro:              #e9ecef  (separadores)
Texto Principal:         #2c2c2c  (legibilidad)
Texto Secundario:        #6c757d  (metadatos)
```

### Estructura Visual (por página)

**Página 1: Portada & Información Principal**
- Logo corporativo (Header)
- Imagen hero grande (140mm de altura)
- Nombre y ubicación de propiedad
- Bloque de precio destacado
- Grid de características clave (4 columnas)
- Footer con QR

**Página 2: Información Detallada**
- Descripción comercial
- Grid de especificaciones técnicas (2 columnas)
- Amenidades
- Observaciones relevantes

**Página 3: Galería (Condicional)**
- Aparece solo si hay más de 1 imagen
- Grid 3×3 de imágenes secundarias

**Página 4: Cierre & Broker (Condicional)**
- Bloque del broker (si `includeBroker = true`)
- Nota legal corporativa
- Footer

---

## 🔧 Requisitos Técnicos

### Dependencias necesarias

```bash
# Si aún no las tienes instaladas:
composer require barryvdh/laravel-dompdf
```

### Configuración en `config/dompdf.php`

```php
'default_media_type' => 'print',
'font_path' => base_path('resources/fonts/'),
'font_cache' => storage_path('app/dompdf-fonts/'),
'enable_html5_parser' => true,
'enable_css_float' => true,
'enable_php' => true,
'enable_local_file_access' => true,
```

### Storage & Assets

**Rutas esperadas para imágenes:**
- Imágenes de propiedades: `storage/app/public/properties/{id}/images/`
- QR: `storage/app/public/properties/{id}/qr/`
- Fotos broker: `storage/app/public/users/{id}/profile-photo/`

**Logo corporativo:**
- Ubicación: `public/images/logo-homedelvalle.png`
- Formato: PNG transparente
- Tamaño recomendado: 200×100px mínimo

---

## 📝 Estructura de Datos - Modelo Property

### Campos esperados en modelo Property

```php
class Property extends Model
{
    // Campos básicos
    public $title;           // string: nombre de la propiedad
    public $slug;            // string: identificador único
    public $tipo_propiedad;  // string: Casa, Departamento, Terreno, etc.
    public $operacion;       // string: venta, renta, etc.
    
    // Ubicación
    public $colonia;         // string: colonia/neighborhood
    public $alcaldia;        // string: alcaldía/municipio
    public $ciudad;          // string: ciudad
    public $direccion;       // string: dirección completa
    
    // Precio
    public $precio;          // numeric: precio en números
    public $moneda;          // string: MXN, USD, etc. (default: MXN)
    
    // Dimensiones
    public $terreno_m2;      // numeric: metros cuadrados de terreno
    public $construccion_m2; // numeric: metros cuadrados construidos
    
    // Espacios
    public $recamaras;       // integer: número de recámaras
    public $baños;           // integer: número de baños completos
    public $medios_baños;    // integer: medios baños
    public $estacionamientos; // integer: número de estacionamientos
    
    // Características
    public $antigüedad;      // string: años de antigüedad o "Nuevo"
    public $nivel;           // string: piso o nivel
    public $uso_suelo;       // string: residencial, comercial, mixto, etc.
    public $estado_conservacion; // string: excelente, bueno, regular, etc.
    public $estatus_legal;   // string: propiedad comprada, en venta, etc.
    
    // Descripción
    public $descripcion;     // text: descripción comercial
    public $amenidades;      // text/json: amenidades (separadas por coma o JSON)
    public $observaciones;   // text: observaciones adicionales
    
    // Multimedia
    public $qr_path;         // string: ruta a archivo QR (storage/...)
    
    // Relaciones
    public function images() // HasMany: fotos de la propiedad
    public function user()   // BelongsTo: propietario/broker
}
```

### Migraciones recomendadas

```php
Schema::table('properties', function (Blueprint $table) {
    // Si no existen:
    $table->string('slug')->unique();
    $table->string('tipo_propiedad')->nullable();
    $table->string('operacion')->default('venta');
    $table->string('colonia')->nullable();
    $table->string('alcaldia')->nullable();
    $table->string('ciudad')->nullable();
    $table->string('direccion')->nullable();
    $table->decimal('precio', 15, 2)->nullable();
    $table->string('moneda')->default('MXN');
    $table->decimal('terreno_m2', 10, 2)->nullable();
    $table->decimal('construccion_m2', 10, 2)->nullable();
    $table->integer('recamaras')->nullable();
    $table->integer('baños')->nullable();
    $table->integer('medios_baños')->nullable();
    $table->integer('estacionamientos')->nullable();
    $table->string('antigüedad')->nullable();
    $table->string('nivel')->nullable();
    $table->string('uso_suelo')->nullable();
    $table->string('estado_conservacion')->nullable();
    $table->string('estatus_legal')->nullable();
    $table->longText('descripcion')->nullable();
    $table->longText('amenidades')->nullable();
    $table->longText('observaciones')->nullable();
    $table->string('qr_path')->nullable();
});
```

---

## 📸 Estructura de Datos - Modelo User (Broker)

### Campos esperados en modelo User

```php
class User extends Model
{
    public $name;              // string: nombre
    public $last_name;         // string: apellido (opcional)
    public $email;             // string: correo
    public $phone;             // string: teléfono fijo
    public $mobile;            // string: celular
    public $position;          // string: cargo (Agente, Gerente, etc.)
    public $role;              // string: rol en el sistema
    public $profile_photo_url; // string: ruta a foto de perfil
    public $photo_path;        // string: alternativa a profile_photo_url
    
    // Método helper recomendado
    public function getFullNameAttribute()
    {
        return trim("{$this->name} {$this->last_name}");
    }
}
```

### Migraciones recomendadas para users

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('last_name')->nullable();
    $table->string('phone')->nullable();
    $table->string('mobile')->nullable();
    $table->string('position')->nullable();
    $table->string('profile_photo_url')->nullable();
    $table->string('photo_path')->nullable();
});
```

---

## 🛣️ Configuración de Rutas

### Agregar a `routes/web.php` o `routes/api.php`

```php
// Rutas de descargas de PDF (protegidas con auth)
Route::middleware(['auth'])->group(function () {
    // Descargar PDF
    Route::get('/properties/{property}/pdf', [PropertySheetController::class, 'downloadPropertySheet'])
        ->name('properties.pdf.download');
    
    // Preview en navegador
    Route::get('/properties/{property}/pdf/preview', [PropertySheetController::class, 'previewPropertySheet'])
        ->name('properties.pdf.preview');
});

// O públicas si lo prefieres:
Route::get('/properties/{property}/pdf', [PropertySheetController::class, 'downloadPropertySheet'])
    ->name('properties.pdf.download');

Route::get('/properties/{property}/pdf/preview', [PropertySheetController::class, 'previewPropertySheet'])
    ->name('properties.pdf.preview');
```

---

## 💻 Ejemplos de Uso

### 1. Generar PDF sin broker (Modo Institucional)

```blade
<!-- En una vista Blade -->
<a href="{{ route('properties.pdf.download', $property) }}" class="btn btn-primary">
    📥 Descargar Ficha (Institucional)
</a>
```

URL: `/properties/casa-luxury-001/pdf`

### 2. Generar PDF con broker actual (Modo Personalizado)

```blade
<a href="{{ route('properties.pdf.download', ['property' => $property, 'include_broker' => 1]) }}" class="btn btn-primary">
    📥 Descargar Ficha (Con mi datos)
</a>
```

URL: `/properties/casa-luxury-001/pdf?include_broker=1`

### 3. Generar PDF con broker específico

```blade
<a href="{{ route('properties.pdf.download', ['property' => $property, 'include_broker' => 1, 'broker_id' => $broker->id]) }}" class="btn btn-primary">
    📥 Descargar Ficha (Con {{ $broker->name }})
</a>
```

URL: `/properties/casa-luxury-001/pdf?include_broker=1&broker_id=42`

### 4. En controlador - Vista previa

```php
return redirect()->route('properties.pdf.preview', ['property' => $property, 'include_broker' => 1]);
```

---

## 🖼️ Gestión de Imágenes

### Almacenamiento recomendado

```
storage/app/public/
├── properties/
│   ├── 1/
│   │   ├── images/
│   │   │   ├── image-1.jpg (hero - imagen principal)
│   │   │   ├── image-2.jpg
│   │   │   └── image-3.jpg
│   │   └── qr/
│   │       └── qr-code.png
│   └── 2/
│       └── ...
└── users/
    ├── 1/
    │   └── profile-photo/
    │       └── photo.jpg
    └── ...
```

### En el modelo Property

```php
class Property extends Model
{
    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }
}

class PropertyImage extends Model
{
    protected $table = 'property_images';
    
    // Ejemplo de estructura
    // Fields: id, property_id, path, order, created_at
}
```

### Crear QR persistente

```php
// Generar y guardar QR de una propiedad
use SimpleSoftwareIO\QrCode\Facades\QrCode;

$property = Property::find($id);
$qrPath = "properties/{$property->id}/qr/qr-code.png";

// Generar QR apuntando a la propiedad
QrCode::size(300)->format('png')->generate(
    route('properties.show', $property),
    storage_path("app/public/{$qrPath}")
);

// Guardar ruta en BD
$property->update(['qr_path' => $qrPath]);
```

---

## 🎯 Flujo de Generación del PDF

### Paso a paso:

1. **Usuario accede a ruta:** `/properties/casa-001/pdf?include_broker=1`

2. **Controlador recibe solicitud:**
   ```php
   $property = Property::findOrFail($id);
   $includeBroker = request()->boolean('include_broker', false);
   $broker = auth()->user(); // O el usuario especificado
   ```

3. **Se pasan datos a la vista Blade:**
   ```php
   $data = [
       'property' => $property,
       'broker' => $broker,
       'includeBroker' => $includeBroker,
   ];
   ```

4. **Blade renderiza HTML:** La plantilla evalúa condicionales y genera HTML con toda la información

5. **dompdf convierte HTML a PDF:** Con opciones optimizadas para impresión

6. **Usuario descarga el PDF**

---

## ✅ Condicionales Blade Explicados

### Sin broker (Modo Institucional)

```blade
@if($includeBroker && $broker)
    <!-- Mostrar bloque broker -->
@endif
```

- Si `$includeBroker = false` → El bloque broker está oculto
- Si `$broker = null` → El bloque broker está oculto
- Solo muestra si ambas condiciones son verdaderas

### Imágenes condicionales

```blade
@if($property->images && $property->images->first())
    <img src="{{ public_path('storage/' . $property->images->first()->path) }}">
@else
    <div class="hero-image-placeholder">...</div>
@endif
```

- Verifica que la imagen exista antes de referenciarla
- Usa `public_path()` porque dompdf necesita rutas absolutas

### Campos opcionales

```blade
@if($property->terreno_m2)
    <div class="spec-item">
        <span>{{ $property->terreno_m2 }} m²</span>
    </div>
@endif
```

- No muestra el campo si está vacío
- Previene "N/A" o valores nulos en el PDF

---

## 🔒 Validación y Seguridad

### En el controlador:

```php
public function downloadPropertySheet(Property $property, Request $request)
{
    // Validar que la propiedad existe
    if (!$property) {
        abort(404);
    }

    // Validar permisos (opcional)
    // if (!auth()->user()->canViewProperty($property)) {
    //     abort(403);
    // }

    // Validar broker si se especifica
    if ($request->has('broker_id')) {
        $broker = User::findOrFail($request->get('broker_id'));
    }

    // ...resto del código
}
```

---

## 🎨 Customización de Estilos

### Colores

Modificar variables CSS en la sección `:root` de la plantilla:

```css
:root {
    --primary-dark: #1a3a52;      /* Cambiar azul marino */
    --primary-light: #0066cc;     /* Cambiar azul eléctrico */
    --neutral-light: #f8f9fa;     /* Cambiar blanco cálido */
    --text-primary: #2c2c2c;      /* Cambiar color texto */
}
```

### Tipografía

Para cambiar la fuente, modificar en:

```css
html, body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
```

O usar Google Fonts (importar en `<style>`):

```css
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
}
```

### Márgenes y espacios

Modificar en `main-info`, `content-section`, `footer-section`, etc.:

```css
.content-section {
    padding: 0 20mm 15mm;  /* Top, Right, Bottom, Left */
}
```

---

## 📊 Resolución de Problemas

### "Imágenes no se muestran en el PDF"

**Causa:** dompdf necesita rutas absolutas, no URL

**Solución:** Usar siempre `public_path()`:
```blade
<!-- ❌ Incorrecto -->
<img src="{{ asset('storage/properties/1/image.jpg') }}">

<!-- ✅ Correcto -->
<img src="{{ public_path('storage/properties/1/image.jpg') }}">
```

### "El QR se ve pixelado"

**Causa:** Baja resolución

**Solución:** Generar QR con mayor tamaño:
```php
QrCode::size(500)->format('png')->generate(...);
```

### "El PDF se ve diferente en impresión"

**Causa:** Falta habilitación de colores en impresión

**Solución:** Ya está incluido en el CSS:
```css
* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
```

### "Las tablas se desmorona en la segunda página"

**Causa:** Saltos de página automáticos

**Solución:** Usar `page-break-before` o `page-break-inside`:
```css
.section {
    page-break-inside: avoid;
}
```

---

## 📌 Buenas Prácticas

1. **Siempre verifica que las imágenes existen** antes de referenciarlas
2. **Usa `public_path()`** en lugar de URLs para dompdf
3. **Valida campos opcionales** con `@if` antes de mostrar
4. **Mantén consistencia visual** en colores y espacios
5. **Prueba con broker y sin broker** antes de publicar
6. **Genera el QR y guárdalo** en BD, no lo calcules cada vez
7. **Usa `page-break-after`** para controlar paginación
8. **Optimiza imágenes** para que el PDF no sea muy pesado

---

## 🚀 Próximos Pasos

1. Verificar que tienes todas las dependencias instaladas
2. Crear/actualizar modelos según la estructura recomendada
3. Ajustar la plantilla Blade a tus campos específicos
4. Probar generación de PDF con y sin broker
5. Customizar colores y logo según identidad de marca
6. Implementar validación de permisos

---

## 📞 Notas Importantes

- El diseño es **totalmente responsive** para PDF (A4 vertical)
- Está optimizado para **impresión a 300 DPI**
- La paleta de colores es **corporativa y premium**
- Soporta **galería condicional** (solo si hay múltiples imágenes)
- El bloque broker es **completamente opcional** y elegante

**¡Listo para producción!**
