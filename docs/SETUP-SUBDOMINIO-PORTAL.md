# Setup del Subdominio del Portal
## miportal.homedelvalle.mx

> **Para:** Alex  
> **Generado por:** PR Portal-1  
> **Estado:** Pasos que debes ejecutar en cPanel una sola vez.

---

## 1. cPanel — Subdominio (ya hecho ✅)

El subdominio `miportal.homedelvalle.mx` ya está apuntando al mismo `public/` de Laravel. No hay nada más que hacer en cPanel para eso.

---

## 2. Variables de entorno en `.env` (producción)

Agrega o verifica estas líneas en el `.env` del servidor:

```env
# Portal del Cliente
PORTAL_DOMAIN=miportal.homedelvalle.mx
PORTAL_URL=https://miportal.homedelvalle.mx
PORTAL_AUTO_CREATE_ACCOUNTS=false

# Cookies cross-subdomain (para SSO entre homedelvalle.mx y miportal.*)
SESSION_DOMAIN=.homedelvalle.mx
```

> **Importante:** `SESSION_DOMAIN=.homedelvalle.mx` (con el punto al inicio) permite que la cookie de sesión sea válida en todos los subdominios de `homedelvalle.mx`. Esto es necesario para que la impersonación funcione en Portal-6.

---

## 3. Después de actualizar `.env`

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
```

---

## 4. Verificación

Visita `https://miportal.homedelvalle.mx/` — debe mostrar la página de login del portal con el diseño de Home del Valle.

Si ves un error 404 o redirige al sitio principal, verifica que:
- El subdominio en cPanel apunte al mismo `public/` que el dominio principal.
- El `.env` tiene `SESSION_DOMAIN=.homedelvalle.mx`.
- Corriste `php artisan route:cache` después de hacer pull.

---

## 5. Password Reset (Laravel)

Las rutas de reset de contraseña (`/restablecer/{token}`) usan el sistema nativo de Laravel (`Password::sendResetLink`). Asegúrate de que en la config de mail (`config/mail.php` o `.env`) el `APP_URL` apunta al dominio principal y que `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME` están configurados.

Para que los enlaces de reset apunten a `miportal.*` en lugar de `homedelvalle.mx`, agrega a `.env`:

```env
APP_URL=https://homedelvalle.mx
```

Y en `config/auth.php`, verifica que `passwords.users.expire` sea razonable (60 minutos es el default).

---

## 6. Activar creación automática de cuentas (Portal-5)

Cuando Portal-5 esté probado y aprobado:

```env
PORTAL_AUTO_CREATE_ACCOUNTS=true
```

Esto activa los listeners `CreatePortalAccountOnCaptacionSigned` y `CreatePortalAccountOnRentalSigned` que crean la cuenta y envían el email de bienvenida automáticamente al firmar.
