# homedelvalle.mx - Documentación Técnica

Índice de documentación técnica del proyecto homedelvalle.mx

## 📚 Documentos Principales

### 1. **QR_IMPLEMENTATION.md** 
Sistema completo de generación y gestión de códigos QR para propiedades.
- Estructura de archivos
- API correcta de endroid/qr-code v6
- Flujo de generación y reutilización
- Métodos del servicio
- Integración con PDF
- Troubleshooting

**Leer si:** Necesitas entender cómo funciona el sistema QR, implementar cambios, o debuggear problemas.

### 2. **CRITICAL_VERSIONS.md**
Referencia de versiones de librerías críticas para evitar problemas de compatibilidad.
- Versiones correctas (PHP, Laravel, etc.)
- Cambios de API por versión
- Historial de problemas
- Cómo verificar compatibilidad
- Tabla de cambios entre versiones

**Leer si:** Vas a actualizar dependencias, experimentas errores POST-DEPLOY, o necesitas entender qué versión debería estar.

### 3. **DEPLOYMENT_GUIDE.md**
Guía paso a paso para desplegar cambios en producción.
- Pre-deployment checks
- Comandos exactos para el servidor
- Post-deployment testing checklist
- Rollback procedures
- Monitoreo post-deploy
- Troubleshooting de errores comunes

**Leer si:** Necesitas desplegar cambios, hubo un error en deploy, o quieres aprender las mejores prácticas.

---

## 🛠️ Stack Técnico

```
PHP 8.2+
Laravel 11.0
endroid/qr-code 6.0.9  ← CRÍTICO
dompdf/dompdf 3.0
intervention/image 3.0
```

## 🚀 Quick Start (Nueva característica)

1. Leer **QR_IMPLEMENTATION.md** → Entender arquitectura
2. Leer **CRITICAL_VERSIONS.md** → Conocer dependencias
3. Implementar cambios
4. Testear localmente
5. Leer **DEPLOYMENT_GUIDE.md** → Desplegar

## 🐛 Quick Troubleshooting

| Problema | Documento |
|----------|-----------|
| Error 500 al generar QR | QR_IMPLEMENTATION.md + CRITICAL_VERSIONS.md |
| Composer install falla | CRITICAL_VERSIONS.md + DEPLOYMENT_GUIDE.md |
| QR no se genera después de deploy | DEPLOYMENT_GUIDE.md (Post-Deployment Testing) |
| Dependencias incompatibles | CRITICAL_VERSIONS.md |
| Necesito rollback | DEPLOYMENT_GUIDE.md (Rollback section) |

## 📋 Archivos Relacionados en el Repo

```
root/
├── QR_IMPLEMENTATION.md      ← Detalles técnicos QR
├── CRITICAL_VERSIONS.md      ← Versiones y compatibilidad
├── DEPLOYMENT_GUIDE.md       ← Cómo desplegar
├── composer.json             ← Definición de dependencias
├── composer.lock             ← Versiones exactas instaladas
├── database/migrations/
│   └── 2026_04_21_*.php      ← Migración QR codes table
├── app/Models/
│   ├── Property.php          ← Relación hasOne PropertyQrCode
│   └── PropertyQrCode.php    ← Modelo QR
├── app/Services/
│   └── PropertyQrService.php ← Lógica de generación QR
├── app/Http/Controllers/Admin/
│   └── PropertyQrController.php
├── resources/views/admin/properties/
│   └── partials/qr-card.blade.php
└── resources/views/properties/
    └── show.blade.php        ← Incluye QR card
```

## ✅ Checklist para Nuevos Desarrolladores

- [ ] Leer QR_IMPLEMENTATION.md completamente
- [ ] Revisar CRITICAL_VERSIONS.md para entender dependencias
- [ ] Probar generar un QR en local
- [ ] Revisar DEPLOYMENT_GUIDE.md antes del primer deploy
- [ ] Hacer un deploy "vacío" (sin cambios) para familiarizarse

## 🔄 Ciclo de Desarrollo

```
1. Feature Branch
   └── Implementar cambio
       └── Testear localmente
           └── Commit + Push

2. Review (si aplica)
   └── Code review
       └── Cambios solicitados

3. Merge a Main
   └── Todos los tests pasan

4. Deploy a Producción
   └── Seguir DEPLOYMENT_GUIDE.md
       └── Post-deployment testing
           └── Monitoreo 24h
```

## 🚨 Errores Comunes y Soluciones Rápidas

### "Call to undefined method create()"
↳ Estás usando API v4/v5 de endroid/qr-code
↳ Lee: CRITICAL_VERSIONS.md - Tabla de cambios API

### "getStream() not found"
↳ Mismo problema, usar `getString()` en v6
↳ Lee: QR_IMPLEMENTATION.md - Métodos correctos

### Permisos denegados en storage
↳ Problema de permisos del servidor
↳ Lee: DEPLOYMENT_GUIDE.md - Permission denied section

### QR no se genera después de git pull
↳ Probablemente composer no se ejecutó
↳ Lee: DEPLOYMENT_GUIDE.md - Paso 2

---

## 📞 Contacto / Escalación

Si un problema NO está en estos documentos:

1. Revisar laravel.log:
   ```bash
   tail -100 storage/logs/laravel.log | grep ERROR
   ```

2. Verificar versiones:
   ```bash
   composer show | grep endroid
   php -v
   ```

3. Revisar git status:
   ```bash
   git status
   git log --oneline -5
   ```

4. Si sigue sin resolver → Abrir issue en el repo con:
   - Error exacto (del log)
   - Versión de PHP
   - Versión de Laravel
   - Últimos commits
   - Pasos para reproducir

---

## 📅 Historial de Cambios

| Fecha | Cambio | Documento |
|-------|--------|-----------|
| 2026-04-21 | Implementación sistema QR | QR_IMPLEMENTATION.md |
| 2026-04-21 | Documentación de versiones | CRITICAL_VERSIONS.md |
| 2026-04-21 | Guía de deployment | DEPLOYMENT_GUIDE.md |

---

## 🎯 Notas Importantes

⚠️ **NUNCA** actualizar `endroid/qr-code` sin revisar PropertyQrService primero

⚠️ **SIEMPRE** ejecutar `composer install` después de `git pull` si hay cambios en composer.json o composer.lock

⚠️ **SIEMPRE** hacer `php artisan migrate --force` en producción si hay nuevas migraciones

✅ **SIEMPRE** seguir los pasos de DEPLOYMENT_GUIDE.md para despliegues

---

## 📖 Lectura Recomendada

**Para todos:**
- DEPLOYMENT_GUIDE.md - Quick Start section

**Para DevOps/SysAdmin:**
- DEPLOYMENT_GUIDE.md (completo)
- CRITICAL_VERSIONS.md

**Para Backend Developers:**
- QR_IMPLEMENTATION.md
- CRITICAL_VERSIONS.md
- DEPLOYMENT_GUIDE.md

**Para nuevos en el proyecto:**
- Toda la documentación en orden
- Luego revisar el código en `app/Services/PropertyQrService.php`

---

Última actualización: 2026-04-21
