<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get category IDs
        $marketing = DB::table('help_categories')->where('slug', 'marketing')->value('id');
        $clientesLeads = DB::table('help_categories')->where('slug', 'clientes-leads')->value('id');
        $automatizaciones = DB::table('help_categories')->where('slug', 'automatizaciones')->value('id');

        if (!$marketing || !$clientesLeads || !$automatizaciones) {
            return;
        }

        $articles = [
            // ─── LEADS: FLUJO COMPLETO ───
            [
                'help_category_id' => $clientesLeads,
                'title' => 'Flujo completo de leads: de formulario a cliente',
                'slug' => 'flujo-completo-leads',
                'sort_order' => 10,
                'content' => <<<'MD'
# Flujo completo de leads: de formulario a cliente

## Que es un "lead" en el CRM?

Un **lead** puede llegar de dos formas:

1. **Formulario publico** — Cuando alguien llena el formulario de contacto en tu sitio web, aterriza como "Lead entrante" en **Marketing > Leads**
2. **Registro manual** — Cuando tu equipo registra un contacto nuevo directamente en **CRM > Clientes**

## Paso a paso: Lead entrante a cliente

### 1. Llega el lead
Un visitante llena el formulario en tu sitio web. El lead aparece en **Marketing > Leads** con un punto azul (sin leer).

### 2. Revisa el lead
Haz clic en "Ver" para ver los datos: nombre, email, telefono, la propiedad que le intereso y el mensaje.

### 3. Contacta al lead
Usa los botones de **WhatsApp** o **Email** que aparecen en la vista del lead para contactarlo rapidamente.

### 4. Convierte a cliente
Si el lead es valido, haz clic en **"Convertir a Cliente"**. Esto te lleva al formulario de nuevo cliente con los datos pre-llenados:
- Nombre
- Email
- Telefono
- UTMs (fuente de donde llego)

### 5. Completa la informacion
En el formulario de nuevo cliente, agrega:
- **Temperatura** (frio, tibio, caliente)
- **Prioridad** (alta, media, baja)
- **Tipo de interes** (compra, venta, renta)
- **Presupuesto** si lo conoces
- **Canal de origen** y **campana** si aplica

### 6. Seguimiento automatico
Una vez creado el cliente:
- El **lead scoring** empieza a funcionar
- Las **automatizaciones** se disparan si aplican
- El equipo puede dar seguimiento desde el perfil del cliente

---

## Donde estan mis leads?

| Seccion | Que encuentra ahi |
|---------|-------------------|
| **Marketing > Leads** | Formularios de contacto del sitio web (solo lectura, inbox) |
| **CRM > Clientes** | Leads/clientes reales con CRUD completo, scoring, tratos |

**Importante:** "Marketing > Leads" es tu buzón de entrada. Los leads reales se gestionan en "CRM > Clientes".
MD,
            ],

            // ─── ESCENARIO: CAMPANA FACEBOOK ───
            [
                'help_category_id' => $marketing,
                'title' => 'Escenario: Campana de Facebook Ads',
                'slug' => 'escenario-campana-facebook',
                'sort_order' => 10,
                'content' => <<<'MD'
# Escenario: Campana de Facebook Ads

## Situacion
Quieres lanzar una campana de Facebook Ads para promover departamentos en la CDMX.

## Paso 1: Crear el canal
1. Ve a **Marketing**
2. En Canales, crea **"Facebook Ads"** tipo "Pagado"
3. Asigna un color (ej: azul)

## Paso 2: Crear la campana
1. Ve a **Campanas > Nueva Campana**
2. Nombre: "FB - Deptos CDMX Abril 2026"
3. Canal: Facebook Ads
4. Presupuesto: $15,000 MXN
5. Fechas: 1 al 30 de abril
6. Activa la campana

## Paso 3: Configurar la landing
1. Asegurate de que tu landing page tiene el **pixel de Facebook** instalado (Configuracion > Integraciones)
2. Los formularios del sitio capturan UTMs automaticamente

## Paso 4: Registrar leads
Cuando lleguen leads:
- Si vienen del formulario web → aparecen en **Marketing > Leads** con la fuente "facebook"
- Convierte a cliente y asigna la campana "FB - Deptos CDMX Abril 2026"
- Si registras manualmente, selecciona canal "Facebook Ads" y la campana

## Paso 5: Medir resultados
En el **Dashboard de Marketing** veras:
- **CPL** (costo por lead): Presupuesto / leads generados
- **ROI**: Si alguno de esos leads cerro operacion
- **Conversion**: De leads a operaciones cerradas

## Ejemplo numerico
- Presupuesto: $15,000
- Leads generados: 30
- CPL: $500
- Operaciones cerradas: 2
- Comision generada: $180,000
- ROI: 1,100%
MD,
            ],

            // ─── ESCENARIO: REFERIDOS ───
            [
                'help_category_id' => $marketing,
                'title' => 'Escenario: Marketing por referidos',
                'slug' => 'escenario-marketing-referidos',
                'sort_order' => 11,
                'content' => <<<'MD'
# Escenario: Marketing por referidos (canal organico)

## Situacion
Tu mejor fuente de leads son los referidos de clientes satisfechos.

## Paso 1: Crear canal de referidos
1. **Marketing > Canales** → crea "Referidos" tipo "Referido"
2. Costo: $0 (es organico)

## Paso 2: Registrar leads referidos
Cuando un cliente te refiera a alguien:
1. Ve a **CRM > Clientes > Nuevo**
2. Registra los datos del lead
3. En **Canal de Origen** selecciona "Referidos"
4. En **Notas** indica quien te refirio

## Paso 3: Medir
En el dashboard veras que "Referidos" tiene:
- CPL = $0
- Alta tasa de conversion (porque vienen recomendados)
- Mejor ROI que canales pagados

## Diferencia con Comisionistas
- **Referidos** — Canal marketing, no pagas por el contacto
- **Comisionistas** — Programa formal con comision (5% o 10%)

Si vas a pagar comision, usa el modulo de **Comisionistas** (en Equipo). Si solo quieres rastrear de donde vino el lead, usa el canal de marketing.
MD,
            ],

            // ─── ESCENARIO: SEGMENTOS + AUTOMATIZACION ───
            [
                'help_category_id' => $automatizaciones,
                'title' => 'Escenario: Nutrir leads frios automaticamente',
                'slug' => 'escenario-nutrir-leads-frios',
                'sort_order' => 10,
                'content' => <<<'MD'
# Escenario: Nutrir leads frios automaticamente

## Situacion
Tienes 50 leads frios que no han respondido en 2 semanas. Quieres reactivarlos sin que tu equipo pierda tiempo.

## Paso 1: Crear el segmento
1. Ve a **Marketing > Segmentos > Nuevo**
2. Nombre: "Leads frios inactivos"
3. Reglas:
   - Temperatura = Frio
   - Dias inactivo > 14
4. Guarda y evalua — veras cuantos clientes cumplen

## Paso 2: Crear la automatizacion
1. Ve a **Marketing > Automatizaciones > Nueva**
2. Nombre: "Reactivacion leads frios"
3. Trigger: **Entra a segmento** → selecciona "Leads frios inactivos"
4. Pasos:
   - **Enviar email**: "Hola {nombre}, tenemos propiedades nuevas que te pueden interesar..."
   - **Esperar 3 dias**
   - **Condicion**: Si abrio el email → seguir, si no → salir
   - **Enviar email**: "Estas son las 3 propiedades mas buscadas en tu zona..."
   - **Esperar 5 dias**
   - **Crear tarea**: "Llamar a {nombre} - ultimo intento de reactivacion"
5. Activa la automatizacion

## Resultado
- Los leads frios reciben emails automaticos
- Si abren el email, reciben mas contenido (se estan calentando)
- Si no responden, se crea una tarea para llamada manual
- Tu equipo solo interviene cuando hay senal de interes

## Medicion
En la vista de la automatizacion veras:
- Cuantos clientes entraron
- En que paso estan
- Cuantos completaron el flujo
MD,
            ],

            // ─── ESCENARIO: LEAD SCORING EN ACCION ───
            [
                'help_category_id' => $automatizaciones,
                'title' => 'Escenario: Lead scoring en accion',
                'slug' => 'escenario-lead-scoring-accion',
                'sort_order' => 11,
                'content' => <<<'MD'
# Escenario: Lead scoring en accion

## Que es el lead scoring?
Es un sistema de puntos que califica a cada cliente basado en sus acciones. Mas puntos = mas listo para comprar/vender.

## Como se acumulan puntos

| Accion del lead | Puntos | Categoria |
|-----------------|--------|-----------|
| Llena formulario de contacto | +15 | Engagement |
| Abre un email | +5 | Engagement |
| Se agenda una visita | +25 | Actividad |
| Completa una visita | +30 | Actividad |
| Se crea un trato/deal | +50 | Actividad |
| Tiene email registrado | +5 | Perfil |
| Tiene telefono | +5 | Perfil |
| Tiene WhatsApp | +3 | Perfil |
| Tiene ciudad | +3 | Perfil |
| Tiene presupuesto definido | +5 | Perfil |

## Los grados

| Grado | Puntos | Que significa | Que hacer |
|-------|--------|--------------|-----------|
| **A** | 80+ | Listo para cerrar | Llamar HOY, agendar cita |
| **B** | 40-79 | Interesado activo | Enviar propiedades, dar seguimiento semanal |
| **C** | 20-39 | Tibio, necesita nutricion | Automatizaciones de contenido |
| **D** | 0-19 | Frio o nuevo | Dejar que las automatizaciones trabajen |

## Ejemplo real
1. **Maria Garcia** llena el formulario web (+15 pts, grado D)
2. Le envias email con propiedades, lo abre (+5 pts)
3. Responde pidiendo informacion (+10 pts, sube a grado C)
4. Agendas una visita (+25 pts, sube a grado B)
5. Completa la visita (+30 pts, grado A!)
6. Creas un trato (+50 pts) → Maria es prioridad #1

## Donde ver el scoring?
**Marketing > Lead Scoring** — Ranking de todos tus leads ordenados por puntuacion, con barras de progreso y grado A/B/C/D.
MD,
            ],

            // ─── FLUJO: CAMPAÑA COMPLETA DE INICIO A FIN ───
            [
                'help_category_id' => $marketing,
                'title' => 'Guia: Campana de marketing de inicio a fin',
                'slug' => 'guia-campana-inicio-fin',
                'sort_order' => 12,
                'content' => <<<'MD'
# Guia: Campana de marketing de inicio a fin

## 1. Define tu objetivo
- Captar propiedades? → Campana dirigida a propietarios
- Atraer compradores? → Campana de propiedades disponibles
- Nutrir base existente? → Campana de email a leads frios

## 2. Configura el canal
**Marketing > Canales** → Asegurate de tener el canal (Facebook, Google, etc.)

## 3. Crea la campana
**Marketing > Campanas > Nueva**
- Nombre descriptivo: "Google Ads - Casas Polanco Q2 2026"
- Presupuesto y fechas

## 4. Prepara la landing (si aplica)
Asegurate de que:
- El formulario de contacto funciona
- El **Google Tag Manager** esta configurado (Configuracion > Integraciones)
- Los UTMs estan en tus links de anuncio: `?utm_source=google&utm_medium=cpc&utm_campaign=casas-polanco`

## 5. Crea un segmento para el follow-up
**Marketing > Segmentos > Nuevo**
- Ejemplo: "Leads de Google ultimaos 7 dias" con regla: Canal = Google, Dias desde registro < 7

## 6. Crea una automatizacion
**Marketing > Automatizaciones > Nueva**
- Trigger: Entra al segmento
- Pasos: Email de bienvenida → Esperar 2 dias → Email con propiedades → Esperar 3 dias → Tarea de llamada

## 7. Lanza la campana
- Activa los anuncios
- Los leads llegan al formulario
- Convierte a cliente asignando campana y canal
- La automatizacion hace el follow-up

## 8. Mide y optimiza
Despues de 2 semanas, revisa:
- **CPL** — Si es muy alto, ajusta segmentacion del anuncio
- **Apertura de emails** — Si es baja, mejora asuntos
- **Conversion a operacion** — Si es baja, revisa el proceso de ventas
- **ROI** — Si es positivo, escala el presupuesto

## Metricas clave
| Metrica | Formula | Bueno | Alerta |
|---------|---------|-------|--------|
| CPL | Gasto / Leads | < $500 | > $1,000 |
| Conversion | Operaciones / Leads | > 5% | < 2% |
| Apertura email | Abiertos / Enviados | > 25% | < 15% |
| ROI | (Comision - Gasto) / Gasto | > 300% | < 100% |
MD,
            ],

            // ─── ESCENARIO: LEAD QUE LLEGA Y NO SE ATIENDE ───
            [
                'help_category_id' => $clientesLeads,
                'title' => 'Escenario: Evitar que un lead se enfrie',
                'slug' => 'escenario-evitar-lead-frio',
                'sort_order' => 11,
                'content' => <<<'MD'
# Escenario: Evitar que un lead se enfrie

## El problema
Un lead llena el formulario el viernes a las 9pm. Nadie lo ve hasta el lunes. Para entonces, ya contacto a otra inmobiliaria.

## La solucion: Automatizacion + Alertas

### 1. Email automatico de confirmacion
Configura una automatizacion:
- **Trigger**: Nuevo cliente creado
- **Paso 1**: Enviar email inmediato: "Gracias {nombre}, recibimos tu solicitud. Un asesor te contactara en las proximas horas."
- Esto le da tranquilidad al lead mientras llega alguien del equipo.

### 2. Tarea automatica
Agrega otro paso a la automatizacion:
- **Paso 2**: Crear tarea: "Contactar a {nombre} - lead nuevo" con prioridad alta
- La tarea aparece en el dashboard del equipo

### 3. WhatsApp rapido
En **Marketing > Leads**, cada lead tiene boton de WhatsApp para contactar al instante.

### 4. Notificacion
El CRM envia notificaciones cuando llegan leads nuevos. Revisa la campana de notificaciones regularmente.

## Tiempos recomendados de respuesta
| Temperatura | Tiempo maximo |
|-------------|---------------|
| Caliente | 30 minutos |
| Tibio | 2 horas |
| Frio | 24 horas |

## Tip pro
Convierte el lead a cliente inmediatamente y asigna temperatura "caliente" si pregunta por una propiedad especifica. Esto activa el lead scoring y las automatizaciones de seguimiento.
MD,
            ],

            // ─── ESCENARIO: MEDIR ROI POR CANAL ───
            [
                'help_category_id' => $marketing,
                'title' => 'Escenario: Medir ROI por canal de marketing',
                'slug' => 'escenario-medir-roi-canal',
                'sort_order' => 13,
                'content' => <<<'MD'
# Escenario: Medir ROI por canal de marketing

## Situacion
Gastas en Facebook, Google, portales inmobiliarios y repartidores. Quieres saber que canal genera mas negocio.

## Configuracion inicial (una sola vez)
1. **Marketing > Canales** — Crea cada canal: Facebook Ads (Pagado), Google Ads (Pagado), Portal Inmuebles24 (Pagado), Referidos (Referido), Sitio Web (Organico)
2. **Marketing > Campanas** — Crea campanas dentro de cada canal con presupuesto y fechas

## Registro diario
Al registrar cada cliente nuevo:
1. Selecciona **Canal de Origen** (de donde llego)
2. Selecciona **Campana** (si aplica)
3. Registra **Costo de Adquisicion** si lo conoces

## Lectura del dashboard
**Marketing** muestra:

### CPL por canal
- Facebook: $600/lead
- Google: $450/lead
- Referidos: $0/lead

### Conversion por canal
- Facebook: 3% (de leads a operacion cerrada)
- Google: 5%
- Referidos: 15%

### ROI
- Facebook: ROI 200% (cada $1 invertido genera $3 de comision)
- Google: ROI 350%
- Referidos: ROI infinito (sin costo)

## Que hacer con los datos
- **Canal con buen ROI** → Aumentar presupuesto
- **Canal con mal ROI** → Optimizar o pausar
- **Canal sin datos** → Asegurar que todos registren el canal al crear clientes

## Recomendaciones del sistema
El dashboard genera recomendaciones automaticas:
- "Invertir mas en Google Ads (mejor conversion)"
- "Pausar campaign X (CPL muy alto)"
- "Referidos es tu mejor canal, incentiva recomendaciones"
MD,
            ],
        ];

        foreach ($articles as $article) {
            DB::table('help_articles')->updateOrInsert(
                ['slug' => $article['slug']],
                [
                    'help_category_id' => $article['help_category_id'],
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'content' => $article['content'],
                    'sort_order' => $article['sort_order'],
                    'view_count' => 0,
                    'is_published' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Also add contextual tips for marketing flows
        $tips = [
            ['context' => 'submissions.show', 'title' => 'Convierte leads rapido', 'content' => 'Usa el boton "Convertir a Cliente" para llevar los datos automaticamente al formulario de nuevo cliente. No copies y pegues manualmente.', 'type' => 'pro_tip'],
            ['context' => 'submissions.index', 'title' => 'Leads sin leer', 'content' => 'Los leads con punto azul son nuevos. Responde en menos de 2 horas para maximizar conversion. Usa el icono de persona para convertir directo a cliente.', 'type' => 'warning'],
            ['context' => 'clients.create', 'title' => 'Canal de origen', 'content' => 'Siempre selecciona el canal de origen al crear un cliente. Sin esto, no podras medir el ROI de tus campanas de marketing.', 'type' => 'tip'],
        ];

        foreach ($tips as $tip) {
            DB::table('help_tips')->updateOrInsert(
                ['context' => $tip['context'], 'title' => $tip['title']],
                array_merge($tip, [
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('help_articles')->whereIn('slug', [
            'flujo-completo-leads',
            'escenario-campana-facebook',
            'escenario-marketing-referidos',
            'escenario-nutrir-leads-frios',
            'escenario-lead-scoring-accion',
            'guia-campana-inicio-fin',
            'escenario-evitar-lead-frio',
            'escenario-medir-roi-canal',
        ])->delete();

        DB::table('help_tips')->whereIn('context', [
            'submissions.show',
            'submissions.index',
        ])->delete();
    }
};
