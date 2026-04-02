<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\HelpTip;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedArticles();
        $this->seedTips();
    }

    private function seedCategories(): void
    {
        $cats = [
            ['name' => 'Primeros pasos', 'slug' => 'primeros-pasos', 'icon' => '🚀', 'sort_order' => 1],
            ['name' => 'Clientes y Leads', 'slug' => 'clientes-leads', 'icon' => '👤', 'sort_order' => 2],
            ['name' => 'Propiedades', 'slug' => 'propiedades', 'icon' => '🏠', 'sort_order' => 3],
            ['name' => 'Operaciones y Pipeline', 'slug' => 'operaciones', 'icon' => '📋', 'sort_order' => 4],
            ['name' => 'Tareas y Seguimiento', 'slug' => 'tareas', 'icon' => '✅', 'sort_order' => 5],
            ['name' => 'Tratos y Negociaciones', 'slug' => 'tratos', 'icon' => '🤝', 'sort_order' => 6],
            ['name' => 'Rentas y Contratos', 'slug' => 'rentas', 'icon' => '📄', 'sort_order' => 7],
            ['name' => 'Marketing y Campanas', 'slug' => 'marketing', 'icon' => '📣', 'sort_order' => 8],
            ['name' => 'Automatizaciones', 'slug' => 'automatizaciones', 'icon' => '⚡', 'sort_order' => 9],
            ['name' => 'Finanzas', 'slug' => 'finanzas', 'icon' => '💰', 'sort_order' => 10],
            ['name' => 'Sitio Web y CMS', 'slug' => 'cms', 'icon' => '🌐', 'sort_order' => 11],
            ['name' => 'Configuracion', 'slug' => 'configuracion', 'icon' => '⚙️', 'sort_order' => 12],
        ];

        foreach ($cats as $c) {
            HelpCategory::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }

    private function seedArticles(): void
    {
        $articles = [
            // ──────────────── PRIMEROS PASOS ────────────────
            ['category' => 'primeros-pasos', 'title' => 'Bienvenido a Home del Valle CRM', 'slug' => 'bienvenido', 'sort_order' => 1, 'content' => <<<'MD'
# Bienvenido a Home del Valle CRM

Este sistema esta disenado para gestionar todo tu negocio inmobiliario desde un solo lugar.

## Que puedes hacer:

- **Clientes** — Registra leads, asigna temperatura, da seguimiento
- **Propiedades** — Publica propiedades con fotos, precios, ubicacion
- **Operaciones** — Pipeline visual de captacion, venta y renta paso a paso
- **Tratos** — Kanban de negociaciones con etapas de cierre
- **Tareas** — Agenda actividades, recordatorios y seguimientos
- **Rentas** — Proceso completo: poliza juridica, contrato, entrega
- **Marketing** — Campanas, segmentos, automatizaciones y lead scoring
- **Finanzas** — Transacciones, comisiones y gastos
- **Sitio Web** — Blog, paginas, formularios de captacion
- **Analytics** — Dashboard con metricas reales

## Tu primer paso:

1. Ve a **Clientes** y registra tu primer lead
2. Ve a **Propiedades** y publica tu primera propiedad
3. Crea una **Operacion** para conectar cliente + propiedad
4. Deja que las **Automatizaciones** hagan el seguimiento por ti
MD],

            ['category' => 'primeros-pasos', 'title' => 'Navegacion del panel', 'slug' => 'navegacion-panel', 'sort_order' => 2, 'content' => <<<'MD'
# Navegacion del panel

## Menu lateral (sidebar)

El menu lateral es tu navegacion principal. Esta organizado por modulos:

- **Dashboard** — Vista general con KPIs y graficas
- **Propiedades** — Listado y gestion de inmuebles
- **Clientes** — Base de datos de leads y contactos
- **Operaciones** — Pipeline kanban de procesos
- **Tratos** — Kanban de negociaciones
- **Tareas** — Lista de pendientes y agenda
- **Rentas** — Procesos de arrendamiento
- **Marketing** — Campanas, segmentos, scoring
- **Finanzas** — Transacciones y comisiones
- **Configuracion** — Ajustes del sistema

## Dashboard principal

Al iniciar sesion ves:
- Contadores: propiedades activas, clientes, operaciones, tareas pendientes
- Graficas de rendimiento mensual
- Tareas proximas a vencer
- Actividad reciente

## Barra superior

- **Notificaciones** — Alertas de menciones, tareas y actividad
- **Perfil** — Edita tus datos, foto, correo y firma
- **Ayuda** — Este centro de ayuda
MD],

            ['category' => 'primeros-pasos', 'title' => 'Roles y permisos', 'slug' => 'roles-permisos', 'sort_order' => 3, 'content' => <<<'MD'
# Roles y permisos

El CRM tiene un sistema de roles con permisos granulares.

## Roles disponibles:

| Rol | Acceso |
|-----|--------|
| **Super Admin** | Acceso total al sistema, configuracion y usuarios |
| **Broker Direccion** | Gestion completa de operaciones, finanzas y reportes |
| **Broker Senior** | Propiedades, clientes, operaciones y tratos |
| **Asesor** | Sus propios clientes, propiedades asignadas y tareas |
| **Usuario** | Acceso basico de solo lectura |
| **Cliente** | Portal del cliente con sus propiedades y documentos |

## Permisos granulares:

Cada rol tiene permisos especificos como:
- `clients.view`, `clients.create`, `clients.edit`, `clients.delete`
- `properties.view`, `properties.create`, `properties.edit`
- `operations.view`, `operations.manage`
- `finance.view`, `finance.manage`
- `settings.manage`, `users.manage`

## Cambiar rol de un usuario:

1. Ve a **Configuracion > Usuarios**
2. Selecciona el usuario
3. Cambia su rol
4. Los permisos se actualizan automaticamente
MD],

            ['category' => 'primeros-pasos', 'title' => 'Como registrar tu primer cliente', 'slug' => 'primer-cliente', 'sort_order' => 4, 'content' => <<<'MD'
# Como registrar tu primer cliente

## Paso 1: Ve a Clientes
En el menu lateral, haz clic en **Clientes**.

## Paso 2: Clic en "Nuevo Cliente"
Llena los datos basicos:
- **Nombre** (obligatorio)
- **Email** — necesario para enviar fichas tecnicas
- **Telefono / WhatsApp** — para contacto directo
- **Temperatura** — Caliente, Tibio o Frio segun que tan cerca este de cerrar
- **Tipo de interes** — Compra, Venta, Renta como propietario, o Renta como inquilino

## Paso 3: Asigna un asesor
Si trabajas en equipo, asigna el lead a un asesor para que le de seguimiento.

## Paso 4: Datos adicionales (opcionales)
- **Presupuesto** minimo y maximo
- **Zona de interes**
- **Notas** — cualquier detalle relevante
- **Canal de marketing** — de donde llego el lead (Facebook, referido, portal, etc.)

## Pro tip:
Marca la **prioridad** como Alta si el cliente necesita atencion inmediata.
MD],

            // ──────────────── CLIENTES Y LEADS ────────────────
            ['category' => 'clientes-leads', 'title' => 'Gestion completa de clientes', 'slug' => 'gestion-clientes', 'sort_order' => 1, 'content' => <<<'MD'
# Gestion completa de clientes

## Vista de listado

La pantalla principal de clientes muestra:
- Nombre, email, telefono
- Temperatura (caliente/tibio/frio) con colores
- Lead score (A/B/C/D)
- Asesor asignado
- Fecha de creacion

### Filtros disponibles:
- Por temperatura
- Por asesor
- Por tipo de interes
- Busqueda por nombre/email/telefono

## Ficha del cliente

Al hacer clic en un cliente ves:
- **Datos de contacto** — nombre, email, telefono, WhatsApp, direccion
- **Propiedades** — propiedades que le pertenecen, que le interesan, o que se le han enviado
- **Operaciones** — procesos de venta/renta/captacion vinculados
- **Tratos** — negociaciones activas
- **Historial de emails** — correos enviados con estado (abierto/no abierto)
- **Interacciones** — llamadas, visitas, notas registradas
- **Lead Score** — puntuacion y grado actual

## Editar un cliente
1. Abre la ficha del cliente
2. Haz clic en "Editar"
3. Modifica los datos necesarios
4. Guarda los cambios

## Enviar propiedades por correo
1. Desde la ficha del cliente, haz clic en "Enviar propiedades"
2. Selecciona las propiedades a incluir
3. Escribe un mensaje personalizado
4. El correo incluye fichas con foto, precio y detalles
5. Se rastrea si el cliente abre el correo
MD],

            ['category' => 'clientes-leads', 'title' => 'Temperaturas de leads', 'slug' => 'temperaturas-leads', 'sort_order' => 2, 'content' => <<<'MD'
# Temperaturas de leads

Las temperaturas te ayudan a priorizar tu tiempo:

| Temperatura | Significado | Accion recomendada |
|-------------|-------------|-------------------|
| **Caliente** | Listo para cerrar, urgente | Contactar HOY, agendar cita |
| **Tibio** | Interesado pero no urgente | Seguimiento cada 2-3 dias |
| **Frio** | Explorando, largo plazo | Nutrir con contenido, revisitar mensual |

## Cuando cambiar la temperatura:

- **Frio a Tibio**: Respondio un email, pidio mas informacion
- **Tibio a Caliente**: Agenda visita, da presupuesto, muestra urgencia
- **Caliente a Tibio**: Dejo de responder, se enfrio

## Automatizacion:
El sistema puede cambiar temperaturas automaticamente con el **lead scoring**. Si un lead abre muchos correos y agenda visitas, sube de temperatura.
MD],

            ['category' => 'clientes-leads', 'title' => 'Lead Scoring', 'slug' => 'lead-scoring', 'sort_order' => 3, 'content' => <<<'MD'
# Lead Scoring: que es y como funciona

El **lead scoring** asigna puntos a cada cliente basado en sus acciones.

## Puntos por accion:

| Accion | Puntos |
|--------|--------|
| Se le envia un mensaje | +2 |
| Abre un email | +10 |
| Responde un mensaje | +30 |
| Llamada completada | +15 |
| Visita agendada | +25 |
| Visita completada | +50 |
| Entra a pipeline | +50 |

## Grados:

- **A (80+)** — Listo para cerrar. Atencion inmediata.
- **B (50-79)** — Interesado activo. Buen momento para empujar.
- **C (20-49)** — Tibio. Necesita mas nutricion.
- **D (0-19)** — Frio o nuevo. Automatizar seguimiento.

## Que hacer con cada grado:

- **A**: Llamar, agendar cita, crear operacion
- **B**: Enviar propiedades personalizadas, invitar a visitar
- **C**: Campanas automaticas de nutricion
- **D**: Flujo de bienvenida automatico

## Donde verlo:
Ve a **Marketing > Lead Scoring** para ver el ranking de todos tus leads con su grado.
MD],

            ['category' => 'clientes-leads', 'title' => 'Segmentos de clientes', 'slug' => 'segmentos-clientes', 'sort_order' => 4, 'content' => <<<'MD'
# Segmentos de clientes

Los segmentos agrupan clientes automaticamente segun reglas que tu defines.

## Para que sirven:
- Enviar campanas a un grupo especifico
- Disparar automatizaciones cuando un cliente entra a un segmento
- Analizar el comportamiento de grupos

## Como crear un segmento:

1. Ve a **Marketing > Segmentos**
2. Haz clic en "Nuevo Segmento"
3. Define las reglas (puedes agregar varias):

### Campos disponibles:
- Nombre, email, telefono, ciudad
- Temperatura del lead
- Tipo de interes
- Score total
- Dias de inactividad
- Tiene operacion (si/no)
- Tiene trato (si/no)
- Canal de marketing

### Operadores:
- Igual a, diferente de
- Contiene, no contiene
- Mayor que, menor que
- Esta vacio, no esta vacio

## Ejemplos utiles:

| Segmento | Reglas |
|----------|--------|
| Leads calientes sin operacion | Temperatura = Caliente + Sin operacion |
| Propietarios CDMX | Interes = Venta + Ciudad contiene "Mexico" |
| Leads frios 30+ dias | Dias inactivo > 30 |
| Score alto sin atencion | Score > 50 + Sin operacion |

## Segmentos del sistema:
El CRM incluye segmentos predefinidos que no se pueden borrar: "Leads sin respuesta", "Leads calientes", "Propietarios activos", etc.
MD],

            // ──────────────── PROPIEDADES ────────────────
            ['category' => 'propiedades', 'title' => 'Crear y editar propiedades', 'slug' => 'crear-propiedades', 'sort_order' => 1, 'content' => <<<'MD'
# Crear y editar propiedades

## Crear una propiedad:

1. Ve a **Propiedades** en el menu lateral
2. Haz clic en **"Nueva Propiedad"**
3. Completa los datos:

### Datos basicos:
- **Titulo** — nombre descriptivo (ej: "Departamento en Polanco 2 recamaras")
- **Tipo** — Casa, Departamento, Terreno, Oficina, Local, Bodega
- **Tipo de operacion** — Venta, Renta, Renta temporal
- **Precio** y moneda (MXN/USD)
- **Descripcion** — texto detallado para publicacion

### Caracteristicas:
- Recamaras, banos, medio bano
- Metros construidos y de terreno
- Estacionamientos
- Antiguedad
- Amueblado (si/no)

### Ubicacion:
- Direccion completa
- Colonia, ciudad, estado, codigo postal

## Editar propiedad:
1. Abre la propiedad
2. Haz clic en "Editar"
3. Modifica lo que necesites
4. Haz clic en "Guardar"

## Estados de la propiedad:
- **Disponible** — lista para vender/rentar
- **Reservada** — en proceso de cierre
- **Vendida/Rentada** — operacion completada
- **No disponible** — fuera del mercado
MD],

            ['category' => 'propiedades', 'title' => 'Fotos y multimedia', 'slug' => 'fotos-multimedia', 'sort_order' => 2, 'content' => <<<'MD'
# Fotos y multimedia

## Subir fotos:

1. En la ficha de la propiedad, ve a la seccion de **Fotos**
2. Haz clic en "Agregar fotos"
3. Selecciona las imagenes (JPG, PNG, WebP)
4. Las fotos se suben automaticamente
5. Arrastra para reordenar

## Video de YouTube:
- En la edicion de la propiedad hay un campo **"URL de YouTube"**
- Pega el enlace del video tour
- Se mostrara embebido en la ficha de la propiedad

## Mejores practicas:
- Sube al menos **10 fotos** por propiedad
- Incluye: fachada, sala, cocina, recamaras, banos, areas comunes
- Usa fotos en **horizontal** (landscape)
- Buena iluminacion natural
- Resolucion minima recomendada: 1200x800 px

## Foto principal:
La primera foto en el orden sera la imagen principal que aparece en listados y correos a clientes.
MD],

            ['category' => 'propiedades', 'title' => 'Propietarios e interesados', 'slug' => 'propietarios-interesados', 'sort_order' => 3, 'content' => <<<'MD'
# Propietarios e interesados

## Asignar propietario:

Cada propiedad puede tener un **propietario** (dueno del inmueble):

1. Edita la propiedad
2. En el campo **"Propietario"** selecciona un cliente existente
3. Guarda los cambios

El propietario aparecera en la ficha de la propiedad y podras ver sus datos de contacto directamente.

## Interesados:

Los clientes interesados en una propiedad se vinculan de varias formas:
- **Envio por correo** — cuando envias la propiedad por email, queda registrado
- **Tratos** — al crear un trato con esa propiedad
- **Operaciones** — al crear una operacion de venta/renta

## Ver relaciones desde la propiedad:

En la ficha de la propiedad puedes ver:
- **Propietario** — datos del dueno
- **Clientes interesados** — quienes han recibido la propiedad por correo
- **Operaciones** — procesos activos vinculados
- **Tratos** — negociaciones en curso

## Ver relaciones desde el cliente:

En la ficha del cliente puedes ver:
- **Propiedades que posee** — donde es propietario
- **Propiedades enviadas** — que se le han mandado por correo
- **Operaciones y tratos** — vinculados
MD],

            ['category' => 'propiedades', 'title' => 'Integracion con EasyBroker', 'slug' => 'easybroker', 'sort_order' => 4, 'content' => <<<'MD'
# Integracion con EasyBroker

EasyBroker es un portal inmobiliario donde puedes publicar tus propiedades. El CRM se integra para sincronizar automaticamente.

## Configurar la integracion:

1. Ve a **Configuracion > EasyBroker**
2. Ingresa tu **API Key** de EasyBroker
3. Guarda la configuracion

## Publicar una propiedad:

1. Abre la propiedad en el CRM
2. Haz clic en **"Publicar en EasyBroker"**
3. La propiedad se sube con todos sus datos y fotos

## Despublicar:

1. Abre la propiedad
2. Haz clic en **"Despublicar de EasyBroker"**
3. Se retira del portal

## Datos que se sincronizan:
- Titulo y descripcion
- Precio y moneda
- Tipo de propiedad y operacion
- Caracteristicas (recamaras, banos, metros, etc.)
- Ubicacion
- Fotos

## Importante:
- La propiedad debe tener fotos para publicarse correctamente
- Los cambios en el CRM NO se sincronizan automaticamente — debes republicar
- El ID de EasyBroker se guarda en la propiedad para referencia
MD],

            // ──────────────── OPERACIONES ────────────────
            ['category' => 'operaciones', 'title' => 'Pipeline de operaciones', 'slug' => 'pipeline-operaciones', 'sort_order' => 1, 'content' => <<<'MD'
# Pipeline de operaciones

El pipeline es donde **cierras negocios**. Cada operacion pasa por etapas hasta completarse.

## Vista Kanban:

Las operaciones se muestran en un tablero kanban donde cada columna es una etapa. Puedes filtrar por tipo de operacion.

## Tipos y etapas:

### Captacion (captar propiedad en exclusiva)
Lead > Contacto > Visita > Revision docs > Avaluo > Mejoras > Exclusiva > Fotos/video > Carpeta lista

### Venta
Lead > Contacto > Visita > Exclusiva > Publicacion > Busqueda > Investigacion > Contrato > Entrega > Cierre

### Renta
Mismas etapas que venta + Activo > Renovacion

## Crear una operacion:

1. Ve a **Operaciones**
2. Haz clic en "Nueva Operacion"
3. Selecciona tipo (captacion/venta/renta)
4. Asigna propiedad, cliente y asesor
5. La operacion empieza en etapa "Lead"

## Avanzar de etapa:

- En la vista de operacion, completa las tareas del **checklist**
- Cuando completas todas las tareas, la operacion avanza automaticamente
- Tambien puedes avanzar manualmente

## Captacion completada:
Cuando una captacion llega a "Carpeta lista", automaticamente puede crear una nueva operacion de venta o renta.
MD],

            ['category' => 'operaciones', 'title' => 'Checklists de etapas', 'slug' => 'checklists-etapas', 'sort_order' => 2, 'content' => <<<'MD'
# Checklists de etapas

Cada etapa de una operacion tiene un checklist de tareas que deben completarse antes de avanzar.

## Como funcionan:

1. Al crear una operacion, se cargan los checklists predefinidos para cada etapa
2. Conforme avanzas, marcas cada item como completado
3. Cuando todos los items estan completos, la operacion avanza a la siguiente etapa automaticamente

## Ejemplo — Captacion, etapa "Visita":
- Confirmar cita con propietario
- Tomar fotos preliminares
- Medir metros del inmueble
- Revisar documentos de propiedad
- Entregar propuesta de servicios

## Personalizar checklists:

Los administradores pueden modificar los checklists predeterminados:

1. Ve a **Configuracion > Checklists**
2. Selecciona el tipo de operacion
3. Selecciona la etapa
4. Agrega, edita o elimina items
5. Los cambios aplican a nuevas operaciones

## Importante:
- Los checklists de operaciones ya creadas no se modifican al cambiar la plantilla
- Puedes agregar items personalizados a una operacion especifica
MD],

            ['category' => 'operaciones', 'title' => 'Timeline y comentarios', 'slug' => 'timeline-comentarios', 'sort_order' => 3, 'content' => <<<'MD'
# Timeline y comentarios

Cada operacion tiene un timeline que registra toda la actividad.

## Que se registra automaticamente:
- Cambios de etapa (con fecha y hora)
- Documentos subidos
- Tareas completadas
- Contratos generados
- Polizas juridicas creadas

## Comentarios:

Puedes agregar comentarios en la operacion para comunicarte con el equipo:

1. Abre la operacion
2. Baja a la seccion de comentarios
3. Escribe tu comentario
4. Usa **@nombre** para mencionar a un companero

### Menciones:
Cuando mencionas a alguien con @, esa persona recibe una **notificacion** en el sistema. Esto es util para:
- Pedir aprobacion: "@director necesito tu visto bueno para el contrato"
- Asignar seguimiento: "@maria por favor agenda la visita"
- Informar: "@equipo el cliente confirmo la oferta"

## Donde ver el timeline:
En la ficha de la operacion, la seccion "Timeline" muestra todo en orden cronologico.
MD],

            // ──────────────── TAREAS ────────────────
            ['category' => 'tareas', 'title' => 'Gestion de tareas', 'slug' => 'gestion-tareas', 'sort_order' => 1, 'content' => <<<'MD'
# Gestion de tareas

Las tareas te ayudan a organizar tus pendientes y no olvidar seguimientos.

## Crear una tarea:

1. Ve a **Tareas** en el menu
2. Haz clic en "Nueva Tarea"
3. Completa:
   - **Titulo** — que hay que hacer
   - **Descripcion** — detalles adicionales
   - **Prioridad** — Baja, Media, Alta, Urgente
   - **Fecha de vencimiento** — cuando debe completarse
   - **Asignado a** — quien la hara (puede ser otro asesor)

## Vincular tareas:

Las tareas pueden vincularse a:
- Un **cliente** — "Llamar a Juan Garcia"
- Una **propiedad** — "Tomar fotos del depto en Polanco"
- Una **operacion** — "Enviar contrato de renta"
- Un **trato** — "Dar seguimiento a oferta"

## Prioridades:

| Prioridad | Uso |
|-----------|-----|
| **Urgente** | Requiere atencion inmediata (vence hoy) |
| **Alta** | Importante, hacer esta semana |
| **Media** | Normal, hacer pronto |
| **Baja** | Puede esperar |

## Completar tareas:
- Haz clic en el check para marcar como completada
- Se registra fecha y hora de completado
- Las tareas vencidas se marcan como **atrasadas** en rojo

## Filtros:
Filtra por estado (pendiente, en progreso, completada), prioridad o operacion.
MD],

            ['category' => 'tareas', 'title' => 'Tareas automaticas', 'slug' => 'tareas-automaticas', 'sort_order' => 2, 'content' => <<<'MD'
# Tareas automaticas

El CRM puede crear tareas automaticamente en dos situaciones:

## 1. Checklists de operaciones
Cuando creas una operacion, cada etapa genera automaticamente las tareas del checklist predefinido.

## 2. Automatizaciones
Puedes configurar automatizaciones que creen tareas:

**Ejemplo:** Cuando un lead nuevo entra, crear tarea "Llamar al cliente en las proximas 24 horas" asignada al asesor.

### Como configurarlo:
1. Ve a **Marketing > Automatizaciones**
2. Agrega un paso de tipo **"Crear Tarea"**
3. Define titulo, descripcion y a quien se asigna
4. La tarea se crea automaticamente cuando el flujo llega a ese paso

## Stats del dashboard:
En el dashboard de tareas puedes ver:
- Total de tareas
- Pendientes
- Atrasadas (vencidas sin completar)
- Completadas esta semana
MD],

            // ──────────────── TRATOS ────────────────
            ['category' => 'tratos', 'title' => 'Tratos y negociaciones', 'slug' => 'tratos-negociaciones', 'sort_order' => 1, 'content' => <<<'MD'
# Tratos y negociaciones

Los tratos representan negociaciones de compraventa o renta con un valor monetario.

## Vista Kanban:

Los tratos se muestran en un tablero con columnas por etapa:

**Lead > Contacto > Visita > Negociacion > Oferta > Cierre > Ganado / Perdido**

## Crear un trato:

1. Ve a **Tratos**
2. Haz clic en "Nuevo Trato"
3. Completa:
   - **Titulo** — nombre descriptivo del trato
   - **Valor** — monto estimado (MXN o USD)
   - **Propiedad** — inmueble relacionado
   - **Cliente** — comprador/inquilino
   - **Asesor** — responsable del trato

## Mover de etapa:

En el kanban, la etapa se puede cambiar desde la ficha del trato. Cuando marcas como "Ganado" o "Perdido":
- Se registra la fecha de cierre automaticamente
- Si lo mueves de vuelta a una etapa activa, se borra la fecha de cierre

## Metricas:

En la parte superior del modulo ves:
- **Total de tratos**
- **Tratos activos** (no ganados ni perdidos)
- **Tratos ganados**
- **Valor del pipeline** (suma de tratos activos)

## Diferencia entre Tratos y Operaciones:
- **Tratos** — para negociaciones simples con valor monetario
- **Operaciones** — para procesos complejos con checklists y etapas detalladas
Puedes usar ambos segun la complejidad del negocio.
MD],

            // ──────────────── RENTAS Y CONTRATOS ────────────────
            ['category' => 'rentas', 'title' => 'Proceso de renta', 'slug' => 'proceso-renta', 'sort_order' => 1, 'content' => <<<'MD'
# Proceso de renta

El modulo de rentas gestiona todo el ciclo de arrendamiento desde la captacion hasta la renovacion.

## Etapas del proceso:

1. **Captacion** — Se consigue la propiedad para rentar
2. **Verificacion** — Revision de documentos del inquilino
3. **Publicacion** — Se publica la propiedad en portales
4. **Busqueda** — Busqueda activa de arrendatario
5. **Investigacion** — Investigacion del candidato / poliza juridica
6. **Contrato** — Elaboracion y firma del contrato
7. **Entrega** — Entrega fisica del inmueble
8. **Activo** — Renta en curso
9. **Renovacion** — Se acerca el vencimiento
10. **Cerrado** — Proceso terminado

## Crear un proceso de renta:

1. Ve a **Rentas**
2. Haz clic en "Nuevo Proceso"
3. Selecciona la propiedad (solo tipo renta/renta temporal)
4. Asigna propietario, inquilino y asesor
5. Define renta mensual y tipo de garantia

## Tipos de garantia:
- **Deposito** — meses de deposito en efectivo
- **Poliza juridica** — a traves de aseguradora
- **Fianza** — fiador solidario

## Vista Kanban:
Los procesos de renta tambien se ven en un tablero kanban por etapa, con filtros por asesor y etapa.
MD],

            ['category' => 'rentas', 'title' => 'Polizas juridicas', 'slug' => 'polizas-juridicas', 'sort_order' => 2, 'content' => <<<'MD'
# Polizas juridicas

La poliza juridica es un seguro que protege al propietario contra incumplimiento del inquilino.

## Estados de la poliza:

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Se creo pero aun no se envian documentos |
| **Documentos enviados** | El inquilino entrego su documentacion |
| **En revision** | La aseguradora esta revisando |
| **Aprobada** | El inquilino fue aprobado |
| **Rechazada** | El inquilino no paso la investigacion |
| **Vencida** | La poliza ya expiro |

## Crear una poliza:

1. Desde el proceso de renta, haz clic en "Crear Poliza"
2. Ingresa:
   - Aseguradora
   - Numero de poliza (cuando se tenga)
   - Costo
   - Fechas de cobertura
3. Actualiza el estado conforme avanza

## Auto-avance:
Cuando la poliza se **aprueba** y el proceso de renta esta en etapa "Investigacion", automaticamente avanza a la etapa "Contrato".

## Timeline:
Cada cambio de estado queda registrado como un evento en el historial de la poliza.
MD],

            ['category' => 'rentas', 'title' => 'Contratos', 'slug' => 'contratos', 'sort_order' => 3, 'content' => <<<'MD'
# Contratos

El CRM permite generar contratos automaticamente o subir contratos elaborados externamente.

## Generar contrato desde plantilla:

1. Desde la operacion o proceso de renta, haz clic en **"Generar Contrato"**
2. Selecciona una plantilla
3. El sistema llena automaticamente las variables:
   - Fecha actual
   - Nombre del propietario e inquilino
   - Direccion de la propiedad
   - Monto de renta mensual
   - Deposito de garantia
   - Fechas de inicio y termino
4. Revisa el contrato generado
5. Descarga como PDF

## Subir contrato externo:

1. Haz clic en "Subir Contrato"
2. Selecciona el archivo (PDF, DOC, DOCX — maximo 20 MB)
3. El archivo se asocia a la operacion o renta

## Firma digital:

1. Abre el contrato
2. Haz clic en "Enviar para firma"
3. Ingresa los datos del firmante (nombre, email)
4. Se registra la firma con IP y fecha

## Plantillas de contrato:
Los administradores pueden crear plantillas en **Configuracion > Plantillas de Contrato** usando variables como:
- `{nombre_propietario}`, `{nombre_inquilino}`
- `{direccion_propiedad}`, `{renta_mensual}`
- `{fecha_inicio}`, `{fecha_termino}`
MD],

            // ──────────────── MARKETING ────────────────
            ['category' => 'marketing', 'title' => 'Canales de marketing', 'slug' => 'canales-marketing', 'sort_order' => 1, 'content' => <<<'MD'
# Canales de marketing

Los canales te permiten rastrear de donde vienen tus leads para medir el retorno de inversion.

## Tipos de canales:
- **Pagado** — Facebook Ads, Google Ads, portales inmobiliarios
- **Organico** — SEO, redes sociales organicas, blog
- **Referido** — Recomendaciones de clientes
- **Directo** — Llegan solos (sitio web, telefono)

## Configurar canales:

1. Ve a **Marketing**
2. En la seccion de canales, crea los tuyos
3. Asigna nombre, tipo y color
4. Al registrar un cliente, selecciona de que canal llego

## Metricas por canal:

El dashboard de marketing muestra para cada canal:
- **Leads totales** — cuantos clientes llegaron
- **Costo total** — cuanto gastaste en campanas de ese canal
- **CPL** (costo por lead) — costo / leads
- **Conversion** — porcentaje que llego a cierre (ganado)
- **Revenue** — ingresos de tratos ganados
- **ROI** — retorno sobre la inversion

## Recomendaciones automaticas:
El sistema sugiere:
- **Aumentar presupuesto** si el ROI es mayor a 200%
- **Pausar canal** si el ROI es negativo
- **Optimizar** si tienes muchos leads pero baja conversion
MD],

            ['category' => 'marketing', 'title' => 'Campanas', 'slug' => 'campanas-marketing', 'sort_order' => 2, 'content' => <<<'MD'
# Campanas de marketing

Las campanas te permiten rastrear y medir esfuerzos especificos de marketing.

## Crear una campana:

1. Ve a **Marketing**
2. Haz clic en "Nueva Campana"
3. Completa:
   - **Nombre** — ej: "Facebook Ads Polanco Marzo 2026"
   - **Canal** — a que canal pertenece
   - **Presupuesto** — cuanto planeas gastar
   - **Gasto real** — cuanto has gastado
   - **Fecha inicio/fin**
   - **Estado** — Activa, Pausada, Completada

## Vincular leads:
Al registrar un cliente, selecciona la campana de donde provino. Esto permite medir:
- Cuantos leads genero la campana
- Costo por lead
- Cuantos cerraron
- Revenue total

## Dashboard de campanas:
Ve el rendimiento de cada campana con:
- Leads generados
- CPL
- Tratos ganados
- Revenue
- ROI

## Mejores practicas:
- Crea una campana por cada esfuerzo medible
- Actualiza el gasto real semanalmente
- Compara CPL entre campanas para optimizar presupuesto
MD],

            ['category' => 'marketing', 'title' => 'Crear campanas automatizadas', 'slug' => 'crear-campana', 'sort_order' => 3, 'content' => <<<'MD'
# Crear campanas automatizadas

Combina segmentos y automatizaciones para crear campanas que trabajan solas.

## Paso 1: Define tu objetivo
- Captar propiedades nuevas?
- Nutrir leads frios?
- Cerrar leads calientes?

## Paso 2: Crea un segmento
Ve a **Marketing > Segmentos** y agrupa a los clientes objetivo.

Ejemplo: Temperatura = Caliente + Sin operacion

## Paso 3: Crea la automatizacion
Ve a **Marketing > Automatizaciones** y crea un flujo:

1. **Trigger**: Entra al segmento
2. **Email**: Mensaje personalizado
3. **Esperar**: 3 dias
4. **Condicion**: Abrio el email?
   - Si: WhatsApp de seguimiento
   - No: Reenviar con otro asunto
5. **Esperar**: 5 dias
6. **Condicion**: Score > 50?
   - Si: Mover a pipeline
   - No: Crear tarea para llamar

## Mejores practicas:
- Maximo 2-3 emails por semana
- Personaliza con nombre y datos reales
- Espacia los mensajes: 1, 3, 7 dias
- Mide tasas de apertura y respuesta
MD],

            ['category' => 'marketing', 'title' => 'Analytics y metricas', 'slug' => 'analytics-metricas', 'sort_order' => 4, 'content' => <<<'MD'
# Analytics y metricas

El dashboard de analytics te da una vision completa de tu negocio.

## KPIs principales:

- **Propiedades activas** — cuantas tienes publicadas
- **Tratos en pipeline** — negociaciones activas
- **Revenue mensual** — ingresos del mes
- **Tasa de conversion** — porcentaje de tratos ganados vs totales

## Graficas disponibles:

### Tratos por etapa
Barra horizontal mostrando cuantos tratos hay en cada etapa del pipeline.

### Ingresos vs Gastos
Grafica de 6 meses comparando entradas y salidas.

### Top 5 asesores
Ranking por comisiones pagadas.

### Top 5 propiedades
Propiedades mas caras en inventario.

### Clientes nuevos por mes
Tendencia de adquisicion de leads (6 meses).

### Propiedades por tipo
Distribucion del inventario (casas, deptos, terrenos, etc.).

### Tareas
Pendientes, en progreso, atrasadas, completadas esta semana.

## Donde verlo:
Ve a **Analytics** en el menu de administracion.
MD],

            // ──────────────── AUTOMATIZACIONES ────────────────
            ['category' => 'automatizaciones', 'title' => 'Como funcionan las automatizaciones', 'slug' => 'como-funcionan-automatizaciones', 'sort_order' => 1, 'content' => <<<'MD'
# Como funcionan las automatizaciones

Las automatizaciones ejecutan acciones cuando se cumple un disparador (trigger).

## Triggers disponibles:

| Trigger | Se ejecuta cuando... |
|---------|---------------------|
| Entra a segmento | Un cliente cumple las reglas de un segmento |
| Cambio de etapa | Se mueve una operacion de etapa |
| Nuevo cliente | Se registra un cliente nuevo |
| Score alcanzado | El lead scoring llega a un umbral |
| Dias sin actividad | El cliente lleva X dias sin interaccion |
| Manual | Tu decides quien entra |

## Pasos disponibles:

- **Esperar** — Pausa X horas/dias antes del siguiente paso
- **Enviar Email** — Correo automatico personalizado
- **Enviar WhatsApp** — Mensaje por WhatsApp
- **Condicion** — Si/No basado en datos del lead
- **Crear Tarea** — Asigna tarea a un asesor
- **Mover a Pipeline** — Crea operacion automaticamente
- **Sumar Puntos** — Agrega puntos de score
- **Actualizar Campo** — Cambia temperatura, prioridad, etc.

## El flujo visual:
En la vista de una automatizacion ves los pasos conectados como un diagrama de flujo, con estadisticas de cuantos clientes pasaron por cada paso.
MD],

            ['category' => 'automatizaciones', 'title' => 'Crear una automatizacion paso a paso', 'slug' => 'crear-automatizacion', 'sort_order' => 2, 'content' => <<<'MD'
# Crear una automatizacion paso a paso

## 1. Ve a Marketing > Automatizaciones
Haz clic en **"Nueva Automatizacion"**.

## 2. Datos basicos:
- **Nombre** — ej: "Bienvenida a leads nuevos"
- **Trigger** — que la dispara
- **Segmento** (si aplica) — a que grupo de clientes aplica

## 3. Agrega pasos:
Haz clic en "Agregar Paso" y selecciona el tipo:

### Email:
- Asunto del correo
- Cuerpo HTML (puedes usar variables: nombre, email, ciudad)

### WhatsApp:
- Plantilla del mensaje
- Variables personalizadas

### Esperar:
- Cantidad y unidad (horas o dias)

### Condicion:
- Campo a evaluar
- Operador (igual, mayor que, etc.)
- Valor

### Crear tarea:
- Titulo de la tarea
- A quien se asigna

### Mover a pipeline:
- Tipo de operacion (venta/renta/captacion)
- Etapa inicial

### Sumar puntos:
- Cantidad de puntos

### Actualizar campo:
- Campo a modificar
- Nuevo valor

## 4. Activa la automatizacion
Usa el toggle para activarla. Los clientes que cumplan el trigger entraran automaticamente.

## 5. Monitorea
En la vista de la automatizacion ves:
- Cuantos estan inscritos
- Cuantos completaron
- Cuantos estan en cada paso
- Cuantos fallaron
MD],

            // ──────────────── FINANZAS ────────────────
            ['category' => 'finanzas', 'title' => 'Transacciones', 'slug' => 'transacciones', 'sort_order' => 1, 'content' => <<<'MD'
# Transacciones

El modulo de finanzas te permite registrar todos los movimientos de dinero.

## Crear una transaccion:

1. Ve a **Finanzas > Transacciones**
2. Haz clic en "Nueva Transaccion"
3. Completa:
   - **Tipo** — Ingreso o Gasto
   - **Monto** y moneda (MXN/USD)
   - **Categoria** — Comision, Renta, Mantenimiento, Marketing, Salario, Oficina, Impuesto, Otro
   - **Metodo de pago** — Efectivo, Transferencia, Cheque, Tarjeta
   - **Fecha**
   - **Descripcion**
   - **Vinculacion** (opcional) — a un trato, propiedad o asesor

## Filtros:
- Por tipo (ingreso/gasto)
- Por categoria
- Por rango de fechas
- Busqueda por descripcion

## Dashboard financiero:

En la vista principal de finanzas ves:
- **Ingresos del mes**
- **Gastos del mes**
- **Comisiones pendientes**
- **Comisiones pagadas**
- Grafica de 6 meses: ingresos vs gastos
- Transacciones recientes
MD],

            ['category' => 'finanzas', 'title' => 'Comisiones', 'slug' => 'comisiones', 'sort_order' => 2, 'content' => <<<'MD'
# Comisiones

Las comisiones se generan cuando se cierra un trato y se le debe pagar al asesor.

## Estados de comision:

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Se genero pero no esta aprobada |
| **Aprobada** | El director aprobo el pago |
| **Pagada** | Se pago al asesor |

## Flujo de comisiones:

1. Se cierra un trato (estado "Ganado")
2. Se crea una comision vinculada al trato y asesor
3. El director revisa y **aprueba** la comision
4. Finanzas procesa el pago y marca como **pagada**
5. Se registra la fecha de pago

## Ver comisiones:

En **Finanzas** puedes ver:
- Lista de todas las comisiones
- Filtrar por estado (pendiente/aprobada/pagada)
- Filtrar por asesor
- Total pendiente vs pagado

## Top asesores:
En analytics puedes ver el ranking de asesores por comisiones pagadas.
MD],

            // ──────────────── CMS ────────────────
            ['category' => 'cms', 'title' => 'Paginas y landing pages', 'slug' => 'paginas-landing', 'sort_order' => 1, 'content' => <<<'MD'
# Paginas y landing pages

El CRM incluye un sistema de gestion de contenido para tu sitio web.

## Crear una pagina:

1. Ve a **CMS > Paginas**
2. Haz clic en "Nueva Pagina"
3. Completa:
   - **Titulo**
   - **Contenido** — editor de texto enriquecido
   - **Slug** — URL de la pagina (se genera automaticamente)
   - **SEO** — meta titulo, descripcion, keywords
   - **Template** — tipo de diseno a usar
   - **Secciones** — bloques de contenido personalizable

## Landing pages:
Las landing pages son paginas especiales disenadas para captar leads:
- Incluyen formularios de contacto
- Pueden vincularse a campanas de marketing
- Tienen campos especificos de conversion

## Navegacion:
Las paginas pueden aparecer en el menu principal del sitio:
- Marca "Mostrar en navegacion" al crear la pagina
- Define el orden con el campo de posicion

## SEO:
Cada pagina tiene campos de SEO:
- **Meta titulo** — titulo que aparece en Google
- **Meta descripcion** — descripcion en resultados de busqueda
- **Palabras clave** — keywords para SEO
MD],

            ['category' => 'cms', 'title' => 'Blog y posts', 'slug' => 'blog-posts', 'sort_order' => 2, 'content' => <<<'MD'
# Blog y posts

El blog te ayuda a generar contenido que atrae leads de forma organica (SEO).

## Crear un post:

1. Ve a **CMS > Posts**
2. Haz clic en "Nuevo Post"
3. Completa:
   - **Titulo**
   - **Contenido** — texto del articulo
   - **Categoria** — clasificacion tematica
   - **Tags** — etiquetas para busqueda
   - **Imagen destacada** — foto principal del post
   - **Estado** — Borrador o Publicado

## Categorias:
Organiza tus posts en categorias como:
- Noticias del mercado
- Consejos para compradores
- Guia de colonias
- Tips para propietarios

## Tags:
Los tags permiten clasificar transversalmente. Un post puede tener multiples tags como "Polanco", "inversiones", "credito hipotecario".

## Blog publico:
Los posts publicados aparecen en la seccion de blog de tu sitio web, accesible para visitantes.
MD],

            ['category' => 'cms', 'title' => 'Menus y navegacion', 'slug' => 'menus-navegacion', 'sort_order' => 3, 'content' => <<<'MD'
# Menus y navegacion

Personaliza los menus de tu sitio web publico.

## Tipos de menu:
- **Header** — menu principal en la parte superior
- **Footer** — menu en el pie de pagina

## Configurar menus:

1. Ve a **CMS > Menus**
2. Selecciona el menu a editar (header o footer)
3. Agrega items:
   - **Titulo** — texto que se muestra
   - **URL** — enlace (puede ser una pagina interna o URL externa)
   - **Orden** — posicion en el menu
4. Guarda los cambios

## Footer:
El footer se configura en **CMS > Footer**:
- Texto de pie de pagina
- Redes sociales (Facebook, Instagram, LinkedIn, YouTube)
- Direccion y telefono de la oficina
- Logo

## Homepage:
La pagina principal se configura en **CMS > Homepage**:
- Secciones personalizables (hero, servicios, propiedades destacadas, testimonios)
- Imagenes y textos de cada seccion
MD],

            ['category' => 'cms', 'title' => 'Formularios de captacion', 'slug' => 'formularios-captacion', 'sort_order' => 4, 'content' => <<<'MD'
# Formularios de captacion

Los formularios permiten a los visitantes de tu sitio dejar sus datos y convertirse en leads.

## Crear un formulario:

1. Ve a **CMS > Formularios**
2. Haz clic en "Nuevo Formulario"
3. Configura los campos que necesitas
4. Genera el codigo para embeber en tu sitio o landing page

## Formulario de contacto del sitio:
Tu sitio web tiene un formulario de contacto que genera **submissions** (envios):

1. Ve a **CMS > Submissions** para ver todos los envios
2. Cada submission contiene: nombre, email, telefono, mensaje
3. Puedes convertir un submission en **cliente** con un clic

## Integracion con marketing:
- Al crear un cliente desde un formulario, se puede asignar automaticamente a un canal de marketing
- Las automatizaciones de "Nuevo cliente" se disparan al convertir
- El lead scoring empieza a funcionar desde ese momento

## Formularios en landing pages:
Las landing pages pueden tener formularios embebidos que capturan leads directamente al CRM.
MD],

            ['category' => 'cms', 'title' => 'Medios y archivos', 'slug' => 'medios-archivos', 'sort_order' => 5, 'content' => <<<'MD'
# Medios y archivos

La biblioteca de medios centraliza todos los archivos de tu sitio web.

## Subir archivos:

1. Ve a **CMS > Medios**
2. Haz clic en "Subir"
3. Selecciona imagenes, PDFs o documentos
4. Se almacenan en la biblioteca central

## Usar medios:
Los archivos de la biblioteca se pueden usar en:
- Posts del blog (imagenes)
- Paginas
- Email templates
- Landing pages

## Tipos soportados:
- Imagenes: JPG, PNG, WebP, GIF
- Documentos: PDF
- Otros: segun configuracion

## Organizacion:
Los medios se listan con su nombre, tamano, fecha de subida y preview. Puedes buscar por nombre.
MD],

            // ──────────────── CONFIGURACION ────────────────
            ['category' => 'configuracion', 'title' => 'Configuracion general del sitio', 'slug' => 'configuracion-general', 'sort_order' => 1, 'content' => <<<'MD'
# Configuracion general del sitio

## Acceder:
Ve a **Configuracion > General** (requiere rol de administrador).

## Datos que puedes configurar:

### Identidad:
- **Nombre del sitio** — aparece en el header y emails
- **Logo** — imagen de tu marca
- **Favicon** — icono de la pestana del navegador

### Contacto:
- **Telefono principal**
- **Email de contacto**
- **Direccion de la oficina**

### Redes sociales:
- Facebook, Instagram, LinkedIn, YouTube, Twitter

### Diseno:
- **Color primario** — color principal del sitio
- **Template** — estilo visual del sitio publico

## Importante:
Los cambios en la configuracion afectan todo el sitio publico y los emails que se envian.
MD],

            ['category' => 'configuracion', 'title' => 'Correo SMTP', 'slug' => 'configurar-smtp', 'sort_order' => 2, 'content' => <<<'MD'
# Configurar el correo SMTP

Para enviar correos a clientes necesitas configurar tu servidor de correo.

## Configuracion global (admin):
1. Ve a **Configuracion > Email**
2. Ingresa los datos SMTP:
   - Servidor: smtp.tudominio.com
   - Puerto: 587 (TLS) o 465 (SSL)
   - Usuario y contrasena
3. Haz clic en **Probar conexion**

## Configuracion por usuario:
1. Ve a **Mi Perfil > Correo Empresa**
2. Ingresa tu correo @homedelvalle.mx
3. Configura servidor SMTP, puerto, encriptacion
4. Prueba la conexion

## Prioridad:
- Si el asesor tiene correo configurado, se usa ese
- Si no, se usa el correo global del admin
- Los emails de automatizaciones usan siempre el global

## Templates de email:
En **Configuracion > Email Templates** puedes personalizar las plantillas de correo que usa el sistema para notificaciones (cambio de contrasena, etc.).
MD],

            ['category' => 'configuracion', 'title' => 'Gestion de usuarios', 'slug' => 'gestion-usuarios', 'sort_order' => 3, 'content' => <<<'MD'
# Gestion de usuarios

## Crear un usuario:

1. Ve a **Configuracion > Usuarios**
2. Haz clic en "Nuevo Usuario"
3. Completa:
   - Nombre y apellido
   - Email (sera su login)
   - Contrasena temporal
   - **Rol** — determina sus permisos
4. El usuario podra cambiar su contrasena al iniciar sesion

## Roles disponibles:
- **Super Admin** — acceso total
- **Broker Direccion** — gestion de equipo y reportes
- **Broker Senior** — operaciones y clientes
- **Asesor** — acceso limitado a sus asignaciones
- **Usuario** — solo lectura
- **Cliente** — portal del cliente

## Activar/desactivar usuarios:
Puedes desactivar un usuario sin borrarlo. El usuario desactivado no puede iniciar sesion pero sus datos se conservan.

## Perfil del usuario:
Cada usuario puede editar desde su perfil:
- Datos personales (nombre, telefono, WhatsApp)
- Foto de perfil
- Bio y titulo
- Firma de email
- Configuracion de correo SMTP personal
- Idioma y zona horaria
MD],

            ['category' => 'configuracion', 'title' => 'Plantillas de contrato', 'slug' => 'plantillas-contrato', 'sort_order' => 4, 'content' => <<<'MD'
# Plantillas de contrato

Las plantillas te permiten generar contratos automaticamente con los datos de la operacion.

## Crear una plantilla:

1. Ve a **Configuracion > Plantillas de Contrato**
2. Haz clic en "Nueva Plantilla"
3. Escribe el contenido del contrato usando **variables**:

## Variables disponibles:

| Variable | Se reemplaza por |
|----------|-----------------|
| `{fecha_actual}` | Fecha del dia |
| `{nombre_propietario}` | Nombre del dueno |
| `{nombre_inquilino}` | Nombre del arrendatario |
| `{direccion_propiedad}` | Direccion completa |
| `{renta_mensual}` | Monto de renta |
| `{monto_deposito}` | Deposito de garantia |
| `{fecha_inicio}` | Inicio del contrato |
| `{fecha_termino}` | Fin del contrato |

## Tipos de plantilla:
- **Arrendamiento** — contrato de renta
- **Comision** — acuerdo de comision con asesor
- **Renovacion** — renovacion de renta existente

## Uso:
Al generar un contrato desde una operacion o renta, seleccionas la plantilla y el sistema llena las variables automaticamente.
MD],

            ['category' => 'configuracion', 'title' => 'Integracion EasyBroker', 'slug' => 'config-easybroker', 'sort_order' => 5, 'content' => <<<'MD'
# Configurar la integracion con EasyBroker

## Obtener tu API Key:

1. Inicia sesion en tu cuenta de EasyBroker
2. Ve a Configuracion > API
3. Copia tu API Key

## Configurar en el CRM:

1. Ve a **Configuracion > EasyBroker**
2. Pega tu API Key
3. Guarda los cambios

## Funcionalidades:
- **Publicar** propiedades desde el CRM a EasyBroker
- **Despublicar** cuando se venda o rente
- El CRM guarda el ID de EasyBroker de cada propiedad publicada

## Datos sincronizados:
Titulo, descripcion, precio, tipo, caracteristicas, ubicacion y fotos.

## Importante:
- Los cambios no se sincronizan automaticamente
- Para actualizar en EasyBroker, debes republicar desde el CRM
- Necesitas una cuenta activa de EasyBroker con acceso a API
MD],

            ['category' => 'configuracion', 'title' => 'Checklists de operaciones', 'slug' => 'config-checklists', 'sort_order' => 6, 'content' => <<<'MD'
# Configurar checklists de operaciones

Los checklists definen las tareas que aparecen en cada etapa de una operacion.

## Acceder:

1. Ve a **Configuracion > Checklists**
2. Selecciona el tipo de operacion (Captacion, Venta, Renta)

## Editar checklists:

Para cada etapa puedes:
- **Agregar items** — nuevas tareas requeridas
- **Editar items** — cambiar el texto
- **Eliminar items** — quitar tareas innecesarias
- **Reordenar** — cambiar el orden de las tareas

## Ejemplo — Venta, etapa "Contrato":
1. Solicitar documentos al comprador
2. Elaborar contrato de compraventa
3. Revision legal del contrato
4. Firma ante notario
5. Pago de escrituracion

## Importante:
- Los cambios solo afectan a **nuevas** operaciones
- Las operaciones existentes conservan su checklist original
- Puedes agregar items extra a una operacion individual sin afectar la plantilla
MD],

            ['category' => 'configuracion', 'title' => 'Notificaciones', 'slug' => 'notificaciones', 'sort_order' => 7, 'content' => <<<'MD'
# Notificaciones

El sistema genera notificaciones automaticas para mantener informado al equipo.

## Tipos de notificaciones:

- **Mencion** — alguien te menciono con @ en un comentario
- **Tarea asignada** — te asignaron una nueva tarea
- **Tarea vencida** — una tarea paso su fecha limite
- **Operacion avanzada** — una operacion cambio de etapa
- **Nuevo lead** — se registro un cliente nuevo (para admins)

## Ver notificaciones:

1. Haz clic en el icono de campana en la barra superior
2. Ves las notificaciones no leidas
3. Haz clic en una para ir al contexto (operacion, tarea, etc.)
4. Marca como leidas individual o todas

## Notificaciones por email:
Algunas notificaciones criticas tambien se envian por correo electronico si tienes configurado el SMTP.

## Donde se generan:
- Al mencionar con @ en comentarios de operaciones
- Al asignar tareas
- Al crear operaciones y cambiar etapas
- Desde automatizaciones que crean tareas
MD],
        ];

        foreach ($articles as $a) {
            $category = HelpCategory::where('slug', $a['category'])->first();
            if (!$category) continue;

            HelpArticle::updateOrCreate(['slug' => $a['slug']], [
                'help_category_id' => $category->id,
                'title' => $a['title'],
                'slug' => $a['slug'],
                'content' => $a['content'],
                'sort_order' => $a['sort_order'],
                'is_published' => true,
            ]);
        }
    }

    private function seedTips(): void
    {
        $tips = [
            // Clientes
            ['context' => 'clients.create', 'title' => 'Datos clave', 'content' => 'Asegurate de capturar telefono/WhatsApp y la temperatura del lead. Sin telefono no puedes dar seguimiento efectivo.', 'type' => 'tip'],
            ['context' => 'clients.create', 'title' => 'Tipo de interes', 'content' => 'Si el cliente es propietario que quiere vender, selecciona "Venta". Si busca comprar, selecciona "Compra". Un cliente puede tener ambos intereses.', 'type' => 'pro_tip'],
            ['context' => 'clients.index', 'title' => 'Filtra por temperatura', 'content' => 'Enfoca tu dia en leads calientes primero, luego tibios. Los frios dejaselos a las automatizaciones.', 'type' => 'pro_tip'],
            ['context' => 'clients.index', 'title' => 'Lead Score', 'content' => 'El grado A/B/C/D al lado del nombre indica que tan activo esta el lead. Prioriza los grado A y B.', 'type' => 'tip'],
            ['context' => 'clients.show', 'title' => 'Historial completo', 'content' => 'Desde la ficha del cliente puedes ver todas sus propiedades, operaciones, emails enviados e interacciones en un solo lugar.', 'type' => 'tip'],
            ['context' => 'clients.show', 'title' => 'Enviar propiedades', 'content' => 'Usa el boton "Enviar propiedades" para mandar fichas tecnicas por correo. El sistema rastrea si el cliente lo abre.', 'type' => 'pro_tip'],

            // Propiedades
            ['context' => 'properties.create', 'title' => 'Fotos profesionales', 'content' => 'Las propiedades con 10+ fotos profesionales se venden 3x mas rapido. Invierte en un fotografo.', 'type' => 'tip'],
            ['context' => 'properties.create', 'title' => 'Propietario', 'content' => 'Asigna al propietario de la propiedad para trazabilidad completa. Podras ver sus datos desde la ficha de la propiedad.', 'type' => 'tip'],
            ['context' => 'properties.create', 'title' => 'Descripcion completa', 'content' => 'Una buena descripcion incluye: ubicacion, amenidades, materiales, vias de acceso y motivo de venta. Las propiedades bien descritas generan mas visitas.', 'type' => 'pro_tip'],
            ['context' => 'properties.index', 'title' => 'EasyBroker', 'content' => 'Publica tus propiedades en EasyBroker directamente desde el CRM. Configura tu API key en Configuracion > EasyBroker.', 'type' => 'tip'],
            ['context' => 'properties.show', 'title' => 'Video tour', 'content' => 'Agrega el URL de YouTube del video tour. Los clientes que ven video tienen 85% mas probabilidad de agendar visita.', 'type' => 'pro_tip'],

            // Operaciones
            ['context' => 'operations.index', 'title' => 'Vista Kanban', 'content' => 'Usa los filtros para ver solo un tipo de operacion (captacion, venta, renta). Asi puedes enfocarte en lo importante.', 'type' => 'tip'],
            ['context' => 'operations.create', 'title' => 'Checklist de cada etapa', 'content' => 'Cada etapa tiene un checklist de tareas. Completalas todas para que la operacion avance automaticamente.', 'type' => 'tip'],
            ['context' => 'operations.create', 'title' => 'Captacion primero', 'content' => 'Si vas a captar una propiedad nueva, empieza con operacion tipo "Captacion". Al completarla, automaticamente crea la de Venta o Renta.', 'type' => 'pro_tip'],
            ['context' => 'operations.show', 'title' => 'Menciones @', 'content' => 'Usa @nombre en los comentarios para notificar a un companero. Recibira una alerta instantanea.', 'type' => 'tip'],

            // Tareas
            ['context' => 'tasks.index', 'title' => 'Prioriza atrasadas', 'content' => 'Las tareas en rojo estan atrasadas. Atiendelas primero o reprogramalas.', 'type' => 'warning'],
            ['context' => 'tasks.create', 'title' => 'Vincula siempre', 'content' => 'Vincula cada tarea a un cliente, propiedad u operacion. Asi aparece en el timeline y puedes darle contexto.', 'type' => 'pro_tip'],

            // Tratos
            ['context' => 'deals.index', 'title' => 'Valor del pipeline', 'content' => 'El valor total del pipeline solo suma tratos activos (no ganados ni perdidos). Usalo para proyectar ingresos.', 'type' => 'tip'],
            ['context' => 'deals.create', 'title' => 'Valor estimado', 'content' => 'Pon el valor real esperado del trato. Si es comision, pon tu comision estimada. Si es venta, el precio de la propiedad.', 'type' => 'tip'],

            // Rentas
            ['context' => 'rentals.create', 'title' => 'Tipo de garantia', 'content' => 'La poliza juridica es la opcion mas segura para el propietario. El deposito es mas rapido pero menos proteccion legal.', 'type' => 'pro_tip'],
            ['context' => 'rentals.show', 'title' => 'Renovacion', 'content' => 'El sistema te alerta 30 dias antes del vencimiento del contrato para que inicies el proceso de renovacion.', 'type' => 'tip'],

            // Marketing
            ['context' => 'marketing.dashboard', 'title' => 'ROI real', 'content' => 'Revisa el costo de adquisicion por canal. Si Facebook cuesta $500/lead y cierra al 2%, pero referidos cuestan $0 y cierran al 15%, enfoca ahi.', 'type' => 'pro_tip'],
            ['context' => 'segments.create', 'title' => 'Segmentos efectivos', 'content' => 'Los mejores segmentos combinan 2-3 criterios. Ejemplo: "Caliente + CDMX + Sin operacion" = leads que necesitan atencion YA.', 'type' => 'pro_tip'],
            ['context' => 'automations.create', 'title' => 'Empieza simple', 'content' => 'Tu primera automatizacion: Nuevo cliente > Esperar 1 dia > Email de bienvenida. Despues la vas complejizando.', 'type' => 'tip'],
            ['context' => 'automations.create', 'title' => 'Delays importan', 'content' => 'No envies todo el mismo dia. Espacia los mensajes: 1 dia, 3 dias, 7 dias. Respetar el tiempo del lead aumenta conversion.', 'type' => 'warning'],
            ['context' => 'scoring.index', 'title' => 'Grado A = Llamar hoy', 'content' => 'Un lead grado A tiene alta intencion. No lo dejes enfriar — llama o agenda cita el mismo dia.', 'type' => 'warning'],

            // Finanzas
            ['context' => 'finance.index', 'title' => 'Registra todo', 'content' => 'Registra cada ingreso y gasto para tener metricas reales. Sin datos no hay decisiones inteligentes.', 'type' => 'tip'],
            ['context' => 'finance.commissions', 'title' => 'Aprueba rapido', 'content' => 'Las comisiones pendientes desmotivan al equipo. Aprueba y paga lo antes posible para mantener la moral alta.', 'type' => 'pro_tip'],

            // Config
            ['context' => 'settings.email', 'title' => 'Prueba primero', 'content' => 'Siempre usa "Probar Conexion" antes de guardar. Un SMTP mal configurado hara que ningun correo llegue.', 'type' => 'warning'],
            ['context' => 'settings.users', 'title' => 'Roles correctos', 'content' => 'Asigna el rol minimo necesario. Un asesor no necesita acceso a finanzas ni configuracion del sistema.', 'type' => 'pro_tip'],
        ];

        foreach ($tips as $t) {
            HelpTip::updateOrCreate(
                ['context' => $t['context'], 'title' => $t['title']],
                $t
            );
        }
    }
}
