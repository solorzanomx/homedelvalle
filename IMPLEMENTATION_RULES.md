# IMPLEMENTATION RULES - homedelvalle.mx

Reglas obligatorias para implementar cualquier feature o cambio en el proyecto.

## 1️⃣ SIEMPRE Verificar Compatibilidad de Versiones

**Antes de escribir UNA SOLA LÍNEA de código:**

```
1. Abrir CRITICAL_VERSIONS.md
2. Buscar la librería que vas a usar
3. Ver versión exacta instalada
4. Revisar si hay cambios de API en esa versión
5. Revisar documentación oficial de esa versión
6. ENTONCES escribir el código
```

**Ejemplo:**
```
❌ INCORRECTO:
- Usuario: "Agrega soporte para generar imágenes dinamicamente"
- Yo: "Ok, voy a usar intervention/image"
- (escribo código sin verificar)

✅ CORRECTO:
- Usuario: "Agrega soporte para generar imágenes dinamicamente"
- Yo: "Verificando intervention/image v3.0... [reviso docs] ... Listo, estos son los métodos disponibles..."
- (escribo código confiable)
```

### Librerías Críticas que REQUIEREN Verificación

Estas librerías tienen historia de cambios de API importantes:

- ✅ **endroid/qr-code** (v4 vs v5 vs v6 completamente diferentes)
- ✅ **intervention/image** (v2 vs v3 breaking changes)
- ✅ **dompdf** (v2 vs v3 cambios)
- ✅ **laravel/framework** (cambios entre versiones LTS)
- ✅ **phpmailer** (si se usa directamente)

### Librerías de Menor Riesgo (pero igual revisar)

- laravel/tinker (complementaria)
- fakerphp/faker (solo dev)
- phpunit/phpunit (solo testing)

## 2️⃣ Secuencia Obligatoria para Cualquier Feature

```
PASO 1: VERIFICAR VERSIONES
├─ Abrir CRITICAL_VERSIONS.md
├─ Buscar cada librería que usarás
└─ Documentar qué métodos existen en esa versión

PASO 2: ENTENDER ARQUITECTURA
├─ Revisar documentos relevantes (QR_IMPLEMENTATION.md, etc)
├─ Ver ejemplos de código similar
└─ Entender patrón del proyecto

PASO 3: IMPLEMENTAR LOCALMENTE
├─ Escribir código
├─ Usar métodos correctos para la versión
└─ Testear

PASO 4: DESPLEGAR
├─ Seguir DEPLOYMENT_GUIDE.md
├─ Hacer post-deployment testing
└─ Monitorear 24h

PASO 5: DOCUMENTAR
└─ Actualizar CRITICAL_VERSIONS.md si hay cambios
```

## 3️⃣ Checklist Antes de Ejecutar Cualquier Código

```
[ ] ¿Verifiqué versiones en CRITICAL_VERSIONS.md?
[ ] ¿Busqué la librería específica y su versión?
[ ] ¿Verifiqué que los métodos existan en esa versión?
[ ] ¿Revisé documentación oficial de esa versión?
[ ] ¿Testée localmente?
[ ] ¿Estoy 100% seguro de que funcionará en producción?
```

Si alguno es NO → DETENERME Y REVISAR PRIMERO

## 4️⃣ Cómo Reportar si Hay Duda

**Cuando haya incertidumbre sobre una librería:**

Yo digo:
```
"Necesito verificar compatibilidad de [librería] v[versión].
Consultando CRITICAL_VERSIONS.md y documentación oficial...
[pausa]
Encontré: [métodos disponibles]
Puedo proceder seguramente implementando: [solución]"
```

**O si hay problema:**
```
"Esta librería tiene breaking changes en v[X].
El código que funciona en v[X-1] no funcionará aquí.
Necesito reescribir [componente] para v[X].
Procediendo...

Cambios específicos:
- Método A → Método B
- Remover método C (no existe en v[X])
- Usar método D en su lugar
```

## 5️⃣ Consecuencias de No Seguir Estas Reglas

### Pasado (QR Code incident)
```
❌ No verificar → Código para v4/v5
❌ No testear métodos → `create()` no existe en v6
❌ No revisar docs → `getStream()` no existe
❌ No desplegar correctamente → 4 intentos fallidos

COSTO: ~45 minutos de debugging
LECCIONES: Ahora hay CRITICAL_VERSIONS.md
```

### Futuro (Si no se sigue la regla)
```
❌ Alguien implementa feature X sin verificar versiones
❌ Código funciona en desarrollo (versión diferente)
❌ Falla en producción (versión exacta diferente)
❌ Requiere rollback de emergencia
❌ Afecta usuarios reales

COSTO: Incidente en producción, confianza, tiempo
SOLUCIÓN: SEGUIR ESTAS REGLAS
```

## 6️⃣ Interfaz de Usuario para Estas Reglas

Cuando me pidas algo:

**Yo puedo responder:**

1. "Verificando compatibilidad... ✅ Puedo hacerlo" (procedo)
2. "Verificando compatibilidad... ❌ Hay breaking change en v[X]" (explico problema)
3. "Verificando compatibilidad... ⚠️ Puedo hacerlo pero requiere cambios en [archivo]" (explico)

**Tú puedes confiar en que:**
- Antes de escribir código, verifiqué versiones
- Antes de sugerir implementación, revisé compatibilidad
- Antes de desplegar, testée la solución
- Los cambios FUNCIONARÁN en producción con v[X]

## 7️⃣ Ejemplo de Aplicación

### Ejemplo 1: Agregar "Generar PDF dinámicamente"

```
USUARIO: "Agrega generación de PDF con datos dinámicos"

YO: "Verificando compatibilidad...
- dompdf: ^3.0 ✓
- laravel/framework: ^11.0 ✓
- No hay breaking changes relevantes
- Métodos disponibles en v3.0:
  * Dompdf\Dompdf
  * loadHtml()
  * render()
  * output()
  
Procedo con implementación segura..."

[Escribo código usando métodos correctos]
[Testeo localmente]
[Despliego siguiendo DEPLOYMENT_GUIDE.md]
```

### Ejemplo 2: Actualizar a Laravel 12 (Hipotético)

```
USUARIO: "Actualiza a Laravel 12"

YO: "No recomendado sin verificación exhaustiva:
- Laravel 11 es LTS, Laravel 12 es nueva versión
- Hay breaking changes entre ambas
- Necesito revisar:
  * Storage API cambios
  * Blade cambios
  * Routing cambios
  * Middleware cambios
  
Antes de tocar nada, necesito:
1. Revisar CRITICAL_VERSIONS.md
2. Actualizar composer.json a ^12.0
3. Revisar cada break change
4. Adaptar código (probablemente 10+ cambios)
5. Testear completamente
6. Deploy cuidadoso

¿Procedemos?"
```

## 📋 Checklist Rápido

Antes de **CUALQUIER cosa**, responderé:

- ¿Qué versión de [librería] usas? → CRITICAL_VERSIONS.md
- ¿Existe el método [X]? → Documentación oficial v[Y]
- ¿Esto funcionará en producción? → Testeo local + DEPLOYMENT_GUIDE.md

Si no puedo responder alguno con 100% certeza → ME DETENGO Y VERIFICO

---

**Última actualización:** 2026-04-21
**Razón:** Implementación QR y problemas de versiones
**Responsable:** Team homedelvalle
