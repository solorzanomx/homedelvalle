# 🎨 Guía de Diseño Editorial - Fichas Técnicas Premium

## Principios de Diseño Aplicados

### 1. **Jerarquía Visual Clara**

La ficha está diseñada para que el ojo del lector siga un flujo natural:

```
1. LOGO (Identidad)
   ↓
2. IMAGEN PRINCIPAL (Impacto visual)
   ↓
3. NOMBRE + UBICACIÓN (Qué es)
   ↓
4. PRECIO (Información clave)
   ↓
5. CARACTERÍSTICAS (Quick scan)
   ↓
6. DESCRIPCIÓN (Contexto)
   ↓
7. ESPECIFICACIONES (Detalles técnicos)
   ↓
8. BROKER (Contacto)
   ↓
9. LEGAL (Fine print)
```

### 2. **Uso de Espacios en Blanco**

El diseño utiliza espacios generosos para:
- ✅ Evitar sensación de saturación
- ✅ Permitir que cada elemento respire
- ✅ Mejorar legibilidad y enfoque
- ✅ Transmitir lujo y calidad

**Márgenes recomendados:** 20mm en todos los lados (ya implementado)

### 3. **Tipografía Profesional**

- **Body text:** Segoe UI 10-11px (legible en impresión)
- **Títulos:** Segoe UI Bold 28px
- **Subtítulos:** Segoe UI Bold 12-16px
- **Etiquetas:** Segoe UI Bold 9-10px uppercase

**Por qué Segoe UI:** Web-safe, legible en PDF, corporativa, moderna

### 4. **Paleta de Colores Estratégica**

| Color | Uso | Razón |
|-------|-----|-------|
| #1a3a52 (Azul Marino) | Headers, títulos, bordes | Confianza, profesionalismo, sobriedad |
| #0066cc (Azul Eléctrico) | Accents, énfasis | Modernidad, energía, foco |
| #f8f9fa (Blanco Cálido) | Fondos principales | Limpieza, elegancia, legibilidad |
| #e9ecef (Gris Claro) | Fondos secundarios | Contraste sutil, separación |
| #2c2c2c (Gris Oscuro) | Texto principal | Legibilidad, sofisticación |
| #6c757d (Gris Medio) | Texto secundario | Jerarquía visual |

**Psicología del color en inmobiliario premium:**
- Azul = Confianza, estabilidad (propiedades = inversión)
- Gris = Modernidad, sofisticación
- Blanco = Lujo, espacio, amplitud

### 5. **Elementos Visuales de Énfasis**

#### Bloque de Precio
```
╔════════════════════════════════════════════╗
║  Precio        MXN $4,500,000              ║
║               [VENTA]                       ║
╚════════════════════════════════════════════╝
```
- Fondo oscuro (#1a3a52)
- Texto blanco
- Separación clara
- Tamaño destacado

**Intención:** El precio es lo más importante para el cliente, debe estar visible e impactante.

#### Grid de Características Clave
```
┌─────────┬─────────┬─────────┬─────────┐
│   📐    │   🏗️    │   🛏️    │   🚿    │
│  2,500  │  1,800  │   4    │   3     │
│ M² Terr │ M² Cons │ Recám  │ Baños   │
└─────────┴─────────┴─────────┴─────────┘
```
- Iconos para escaneo rápido
- Números grandes para impacto
- Etiquetas pequeñas para claridad
- Bordes izquierdos para dinamismo

**Intención:** Quick reference visual, el comprador identifica lo importante en 3 segundos.

### 6. **Uso de Bordes y Líneas**

- **Líneas horizontales gruesas:** Separación de secciones principales
- **Líneas finas:** Separadores sutiles en especificaciones
- **Bordes izquierdos en tarjetas:** Dinamismo sin saturación
- **Sin bordes cuadrados:** Apariencia más elegante

### 7. **Fotografía y Composición**

**Imagen Hero (Página 1):**
- Tamaño: 140mm de alto (máximo impacto)
- Proporción: Full-width
- Objetivo: Transmitir calidad y estilo de la propiedad
- Fallback: Degradado gris elegante si falta

**Galería Secundaria (Página 3):**
- Grid 3×3 (9 imágenes)
- Cuadradas (1:1 ratio)
- Espaciadas uniformemente
- Fallback individual para cada falta

### 8. **Densidad Visual**

La plantilla balancea:
- **No demasiado vacía:** Sería aburrida e improductiva
- **No demasiado llena:** Sería caótica y cansadora
- **Punto óptimo:** Profesional, accesible, premium

### 9. **Formato de Página**

**A4 Vertical (210×297mm):**
- Estándar internacional
- Fácil de imprimir
- Fácil de compartir digitalmente
- Compatible con todos los navegadores

**Page breaks automáticos:**
- Página 1: Portada + Info principal (sin romper)
- Página 2: Información detallada (sin romper)
- Página 3: Galería (condicional)
- Página 4: Cierre + Broker + Legal

### 10. **Estructura de Contenido**

```
Página 1: IMPACTO VISUAL
├── Identidad (Logo)
├── Imagen principal (Emocional)
├── Información básica (Factual)
├── Precio (Determinante)
└── Características (Quick scan)

Página 2: INFORMACIÓN
├── Descripción (Narrativa)
├── Especificaciones (Técnico)
├── Amenidades (Beneficios)
└── Observaciones (Detalles)

Página 3: GALERÍA (Emocional)
├── Más imágenes (Confianza)
└── Composición armónica (Estética)

Página 4: CIERRE (Acción)
├── Broker (Humanización)
├── Contacto (Call-to-action)
└── Legal (Confianza)
```

---

## Recomendaciones de Contenido

### Descripción Comercial (150-250 caracteres)

**✅ Bueno:**
> "Espectacular casa ubicada en las Lomas, con diseño arquitectónico contemporáneo, amplios espacios con vista al bosque. Perfecta para familia que busca tranquilidad sin perder acceso a la ciudad."

**❌ Malo:**
> "Casa de 1800m2, 4 recámaras, 3 baños, cerca de escuelas, buenos servicios, mucho potencial, vea"

**Elementos clave:**
- Ubicación + aspiración
- Arquitectura/diseño
- Características diferenciadores
- Lifestyle/beneficios
- Llamada a la acción implícita

### Amenidades (Máximo 6-8)

**Formato recomendado:**
```
✓ Sala de cine
✓ Spa y sauna
✓ Gym privado
✓ Estacionamiento cubierto
✓ Jardinería profesional
✓ Automatización de hogar
```

**Por qué pocos:** Permite que cada uno respire, impacto visual mayor.

### Observaciones Técnicas

Para campos que no tienen sección:
- Estatus legal
- Documentación requerida
- Trabajos necesarios
- Restricciones especiales

**Ejemplo:**
> "Propiedad con posibilidad de dividirse. Escrituras en trámite. Apto para proyecto de desarrollo."

---

## Customización Avanzada

### Cambiar Color Corporativo

1. Abre `property-sheet.blade.php`
2. Busca `:root {` en `<style>`
3. Cambia `--primary-dark` a tu azul
4. Cambia `--primary-light` a tu acento
5. Todas las referencias se actualizan automáticamente

**Ejemplo para Coldwell Banker:**
```css
:root {
    --primary-dark: #1e3a6f;    /* Azul oscuro CB */
    --primary-light: #0055cc;   /* Azul CB */
}
```

### Añadir Más Secciones

Copiar esta estructura:
```blade
<div class="content-section">
    <h2 class="section-title">Tu Sección</h2>
    <p class="description-text">
        {{ $property->campo_nuevo ?? 'N/A' }}
    </p>
</div>
```

### Ajustar Márgenes

En los elementos principales:
```css
.main-info {
    padding: 20mm 20mm 15mm;  /* Cambiar aquí */
}
```

### Añadir Nuevo Campo de Propiedad

```blade
@if($property->tu_nuevo_campo)
    <div class="spec-item">
        <span class="spec-label">TU ETIQUETA</span>
        <span class="spec-value">{{ $property->tu_nuevo_campo }}</span>
    </div>
@endif
```

---

## Casos de Uso Ejemplares

### Casa Luxury Lomas (4M+)
- Imagen principal: Fachada principal con paisaje
- Descripción: Énfasis en arquitectura y vista
- Amenidades: Spa, cine, gym, automatización
- Broker: Foto profesional, datos completos

### Departamento Polanco (1.5M)
- Imagen principal: Sala con vista a ciudad
- Descripción: Ubicación + acceso
- Características: 3 recámaras, 2.5 baños, 180m2
- Amenidades: Gym, alberca, concierge

### Terreno Lomas (8M)
- Imagen principal: Perspectiva aérea del terreno
- Descripción: Potencial de desarrollo
- Especificaciones: Uso de suelo, permisos
- Observaciones: Servicios disponibles

---

## Validación de Calidad

Antes de generar PDF, verificar:

### Contenido
- [ ] Título de propiedad es claro y atractivo
- [ ] Descripción vende el lifestyle, no solo datos
- [ ] Imágenes son de alta calidad (mínimo 1200×800px)
- [ ] Broker tiene foto profesional
- [ ] Datos de contacto están correctos

### Diseño
- [ ] Logo es legible
- [ ] Colores contrastan bien
- [ ] Tipografía es consistente
- [ ] Espacios en blanco respiran
- [ ] Sin elementos saturados

### Funcionalidad
- [ ] PDF genera sin errores
- [ ] Todas las imágenes aparecen
- [ ] QR es escaneable
- [ ] Links funcionan (si aplica)
- [ ] Imprime correctamente a 300 DPI

---

## Inspiración Editorial

El diseño se inspira en:

**Publicaciones Premium:**
- Architectural Digest (espacios limpios, tipografía)
- Financial Times (corporativo, premium)
- Luxury Real Estate Brochures (estructura, jerarquía)

**Principios de Diseño:**
- Design Thinking (user-centric)
- Dieter Rams: "Good Design is as little design as possible"
- Gestalt (proximidad, contraste, alineación)

---

## Performance Impresión

Para que el PDF se vea perfecto impreso:

1. **Resolución:** 300 DPI (ya configurado)
2. **Papel:** A4 blanco brillante recomendado
3. **Colores:** Usar impresora láser a color
4. **Márgenes:** No ajustar en print dialog

**Configuración recomendada en navegador:**
- Márgenes: Mínimo (algunos navegadores)
- Encabezados/Pies: Desactivados
- Fondo: Activado
- Escala: 100%

---

## Evitar Estos Errores Comunes

❌ Imágenes de mala calidad → Usa mínimo 1200×800px
❌ Descripción demasiado técnica → Vende el lifestyle
❌ Demasiadas amenidades → Máximo 8, las mejores
❌ Fotos del broker casual → Usa foto profesional
❌ Fuentes inconsistentes → Usar solo una familia
❌ Bloques saturados → Dar respiro visual
❌ Colores sin contraste → Testing en B&W
❌ QR pequeño → Mínimo 50×50mm para escanear

---

## Notas Finales

Este diseño es resultado de:
- 🎓 Principios de diseño editorial establecidos
- 💼 Estándares de inmobiliaria boutique
- 📐 Experiencia en UX/diseño PDF
- ✅ Optimizado para impresión y digital

**Es profesional. Es elegante. Es listo para usar.**

---

¿Preguntas de diseño? Revisar la plantilla Blade y personalizar según tu identidad de marca.

