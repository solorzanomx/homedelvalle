# VERSIONES CRÍTICAS - homedelvalle.mx

Archivo de referencia de versiones de librerías críticas para evitar problemas futuros.

> **Última actualización:** 2026-04-29
> **Documentos hermanos:** `IMPLEMENTATION_RULES.md`, `CONTEXTO_PROYECTO.md`, `docs/02-MANUAL-IMPLEMENTACION-SITIO.md`.

## PHP y Laravel

```
PHP: ^8.2 (instalado: 8.3.30, máximo antes de 9.0)
Laravel: ^13.0 (instalado: 13.6.0)
```

## Frameworks reactivos y de UI

### Livewire 4
```
livewire/livewire: ^4.2 (instalado: 4.2.4)
- Usado en: Portal del Cliente (miportal.homedelvalle.mx) para componentes
  reactivos autenticados (DocumentUploader, MessageComposer, PaymentTracker,
  NotificationsBell, IncidentReportForm, etc.).
- NO usado en: sitio público (que usa Alpine.js) ni CRM admin (que usa Blade
  + CSS puro). Excepción aprobada: kanban del backend de rentas si requiere
  reactividad — discutir con Alex caso por caso.
- Cambio importante de v3 a v4: nueva sintaxis de propiedades públicas,
  WithFileUploads movido al trait, validación reactiva mejorada.
- Doc: https://livewire.laravel.com
```

### Filament 5
```
filament/filament: ^5.6 (instalado: 5.6.1)
filament/spatie-laravel-media-library-plugin: 5.6.1
- Instalado pero NO es el admin primario. El CRM admin custom vive en
  layouts/app-sidebar.blade.php con Blade + CSS puro.
- Usado puntualmente para: resources específicos cuando se justifique
  (consultar a Alex antes de crear nuevos resources).
- NO migrar el CRM custom a Filament sin decisión explícita.
- Doc: https://filamentphp.com
```

### Tailwind CSS 4
```
tailwindcss: ^4.0 (instalado: 4.2.2)
@tailwindcss/vite: ^4.0 (instalado: 4.2.2)
@tailwindcss/typography: ^0.5.19
- Usado en: sitio público y Portal del Cliente.
- NO usado en: CRM admin (que usa CSS puro con variables CSS).
- Cambio MUY importante de v3 a v4: el config se hace en CSS con `@theme`,
  NO en `tailwind.config.js`. Cualquier `tailwind.config.js` heredado debe
  eliminarse.
- Doc: https://tailwindcss.com/docs/v4-beta
```

### Vite
```
vite: ^8.0 (instalado: 8.0.3)
laravel-vite-plugin: ^3.0
- Bundle del sitio público + portal + admin.
- Para builds de producción: `npm run build`.
```

### Alpine.js
```
- Cargado vía CDN en el sitio público.
- Usado para componentes ligeros (chatbot calificador, lead-popup,
  whatsapp-button, formularios públicos).
- NO se usa en el portal (Livewire 4 reemplaza esa función).
```

## Librerías de Generación de Contenido

### QR Codes
```
endroid/qr-code: ^6.0 (instalado: 6.0.9)
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
dompdf/dompdf: ^3.0 (instalado: 3.1.5)
- Uso: PropertyFichaController para fichas técnicas, contratos generados,
  recibos de renta, reportes mensuales al propietario.
```

### Imágenes
```
intervention/image: ^3.0 (instalado: 3.11.7)
- Uso: ImageOptimizer para optimización de fotos.
- Cambio importante de v2 a v3: API renovada.
```

### Spatie Media Library
```
spatie/laravel-medialibrary: ^11.0 (instalado: 11.21.2)
- Uso: uploads de propiedades, briefs B2B (collection 'briefs'), documentos
  del portal del cliente, recibos generados, archivos adjuntos en mensajes.
- NUNCA usar Storage::put a mano para uploads. Siempre vía Media Library.
- Doc: https://spatie.be/docs/laravel-medialibrary/v11
```

### Browsershot
```
spatie/browsershot: ^5.2 (instalado: 5.2.3)
puppeteer: ^24.42
- Uso: screenshots y PDFs avanzados (cuando DomPDF no alcanza).
```

### Editor WYSIWYG
```
tinymce: ^8.3.2 (self-hosted GPL en public/vendor/tinymce/)
- Uso: editor de blog, templates de email, descripciones de propiedades.
- NO actualizar a >9.x sin revisar plugins activos.
- NO editar public/vendor/tinymce/ a mano. Si subes versión, copiar
  desde node_modules/tinymce/.
```

### Email
```
phpmailer/phpmailer: ^6.9
- Uso: envío de emails con SMTP dinámico desde tabla `email_settings`.
- NO usamos Laravel Mail. Control directo sobre SMTP por
  cliente/usuario.
```

### Iconos
```
lucide-static: ^1.7
- SVG inline desde node_modules/lucide-static/icons/{name}.svg
- Componente Blade <x-icon name="..." class="..." />
```

### Otros
```
google/apiclient: ^2.15
- Integración con Google Signature Requests.

phpoffice/phpword: opcional según necesidad
```

## Dependencias Indirectas Importantes

```
bacon/bacon-qr-code: ^3.1
- Depende de: endroid/qr-code
- Driver QR subyacente
```

## Laravel Core

```
laravel/framework: ^13.0 (instalado: 13.6.0)
- Migrate, Storage, Blade, etc.
- Bootstrap minimalista (bootstrap/app.php) — middleware se registra
  por alias o grupo, NO con $this->middleware() en controlador.
- Routes cargados desde withRouting() en bootstrap/app.php.
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

### Migración de Laravel 11 a Laravel 13
**Fecha:** abril 2026
**Cambios principales:**
- Bootstrap minimalista (bootstrap/app.php sustituye a Kernel.php).
- Middleware se registra con `withMiddleware(...)` o por alias.
- Eliminación de `app/Http/Kernel.php`.
- Aprovechar nuevas features de Carbon y Eloquent sin breaking changes mayores.

### Regla General
**Antes de actualizar una librería crítica:**
1. Revisar CHANGELOG de la nueva versión
2. Buscar cambios en la API
3. Testear generación de QR / PDFs / uploads / formularios antes de deploy
4. Verificar compatibilidad con Livewire 4 si la librería toca componentes del portal

## Cómo Verificar Compatibilidad

```bash
# Ver qué packages dependen de uno específico
composer depends endroid/qr-code

# Ver changelog
composer show --all --format=json | jq '.[] | select(.name=="endroid/qr-code")'
```

## Monitoring de Cambios

Para evitar problemas en el futuro:

1. **Revisión semanal:**
   ```bash
   composer outdated  # Ver qué está desactualizado
   npm outdated       # Ver paquetes JS desactualizados
   ```

2. **Antes de cualquier deploy:**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:clear
   ```

3. **Testing post-deploy:**
   - [ ] QR generation works
   - [ ] QR download works (PNG y SVG)
   - [ ] QR regeneration works
   - [ ] Admin page loads without errors
   - [ ] Sitio público carga sin errores en consola
   - [ ] Portal del Cliente (miportal.*) responde 200 con SSL válido
   - [ ] Componentes Livewire del portal funcionan sin errores en consola

## Documentación Oficial

- Laravel 13: https://laravel.com/docs/13.x
- Livewire 4: https://livewire.laravel.com
- Filament 5: https://filamentphp.com
- Tailwind 4: https://tailwindcss.com/docs/v4-beta
- Vite 8: https://vite.dev
- endroid/qr-code v6: https://github.com/endroid/qr-code
- dompdf: https://github.com/dompdf/dompdf
- intervention/image: https://image.intervention.io
- Spatie Media Library: https://spatie.be/docs/laravel-medialibrary/v11

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

- **No actualices endroid/qr-code sin revisar PropertyQrService primero.**
- **PHP 9.0 puede romper todo — testear antes.**
- **Laravel 14+ puede tener cambios en Storage, Blade o middleware.**
- **Livewire 5 (cuando salga) puede tener breaking changes en propiedades reactivas.**
- **Tailwind 5 puede cambiar la sintaxis de `@theme` — verificar antes de actualizar.**
- **Mantén un backup de composer.lock y package-lock.json antes de actualizar.**
- **El Portal del Cliente vive en subdominio `miportal.homedelvalle.mx` — cualquier cambio que afecte sesiones, cookies o auth debe probarse cross-subdomain.**
