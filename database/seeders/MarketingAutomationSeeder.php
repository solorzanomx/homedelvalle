<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\HelpTip;
use App\Models\LeadScoreRule;
use App\Models\Segment;
use App\Services\LeadScoringService;
use Illuminate\Database\Seeder;

class MarketingAutomationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLeadScoreRules();
        $this->seedDefaultSegments();
        $this->seedHelpCategories();
        $this->seedHelpArticles();
        $this->seedHelpTips();
    }

    private function seedLeadScoreRules(): void
    {
        foreach (LeadScoringService::getDefaultRules() as $rule) {
            LeadScoreRule::updateOrCreate(['event' => $rule['event']], $rule);
        }
    }

    private function seedDefaultSegments(): void
    {
        $segments = [
            [
                'name' => 'Leads sin respuesta (7+ dias)',
                'slug' => 'leads-sin-respuesta',
                'description' => 'Clientes creados hace mas de 7 dias sin ninguna interaccion registrada.',
                'rules' => [
                    ['field' => 'days_inactive', 'operator' => 'greater_than', 'value' => 7],
                ],
                'is_system' => true,
            ],
            [
                'name' => 'Leads calientes',
                'slug' => 'leads-calientes',
                'description' => 'Clientes marcados como calientes o con score A.',
                'rules' => [
                    ['field' => 'lead_temperature', 'operator' => 'equals', 'value' => 'caliente'],
                ],
                'is_system' => true,
            ],
            [
                'name' => 'Propietarios activos',
                'slug' => 'propietarios-activos',
                'description' => 'Clientes con interes de tipo venta o renta como propietario.',
                'rules' => [
                    ['field' => 'interest_types', 'operator' => 'contains', 'value' => 'venta'],
                ],
                'is_system' => true,
            ],
            [
                'name' => 'Score alto sin operacion',
                'slug' => 'score-alto-sin-operacion',
                'description' => 'Leads con score B o superior que aun no tienen operacion activa.',
                'rules' => [
                    ['field' => 'total_score', 'operator' => 'greater_than', 'value' => 50],
                    ['field' => 'has_operation', 'operator' => 'equals', 'value' => false],
                ],
                'is_system' => true,
            ],
            [
                'name' => 'Leads tibios CDMX',
                'slug' => 'leads-tibios-cdmx',
                'description' => 'Leads tibios ubicados en Ciudad de Mexico.',
                'rules' => [
                    ['field' => 'lead_temperature', 'operator' => 'equals', 'value' => 'tibio'],
                    ['field' => 'city', 'operator' => 'contains', 'value' => 'Mexico'],
                ],
                'is_system' => true,
            ],
        ];

        foreach ($segments as $s) {
            Segment::updateOrCreate(['slug' => $s['slug']], $s);
        }
    }

    private function seedHelpCategories(): void
    {
        $cats = [
            ['name' => 'Primeros pasos', 'slug' => 'primeros-pasos', 'icon' => '🚀', 'sort_order' => 1],
            ['name' => 'Clientes y Leads', 'slug' => 'clientes-leads', 'icon' => '👤', 'sort_order' => 2],
            ['name' => 'Propiedades', 'slug' => 'propiedades', 'icon' => '🏠', 'sort_order' => 3],
            ['name' => 'Marketing y campanas', 'slug' => 'marketing', 'icon' => '📣', 'sort_order' => 4],
            ['name' => 'Automatizaciones', 'slug' => 'automatizaciones', 'icon' => '⚡', 'sort_order' => 5],
            ['name' => 'Operaciones y Pipeline', 'slug' => 'operaciones', 'icon' => '📋', 'sort_order' => 6],
            ['name' => 'Configuracion', 'slug' => 'configuracion', 'icon' => '⚙️', 'sort_order' => 7],
        ];

        foreach ($cats as $c) {
            HelpCategory::updateOrCreate(['slug' => $c['slug']], $c);
        }
    }

    private function seedHelpArticles(): void
    {
        $articles = [
            // Primeros pasos
            ['category' => 'primeros-pasos', 'title' => 'Bienvenido a Home del Valle CRM', 'slug' => 'bienvenido', 'sort_order' => 1, 'content' => <<<'MD'
# Bienvenido a Home del Valle CRM

Este sistema esta disenado para ayudarte a **captar propiedades**, **dar seguimiento a leads** y **cerrar operaciones** de manera eficiente.

## Que puedes hacer aqui:

1. **Registrar clientes** — Cada persona interesada en vender, comprar o rentar es un lead
2. **Publicar propiedades** — Captura las propiedades en exclusiva con fotos y datos completos
3. **Crear operaciones** — Lleva el proceso de venta/renta/captacion paso a paso
4. **Automatizar seguimiento** — Configura campanas que nutran a tus leads automaticamente
5. **Medir resultados** — Dashboard con metricas reales de tu negocio

## Tu primer paso:

Ve a **Clientes** y registra tu primer lead. Desde ahi todo fluye.
MD],

            ['category' => 'primeros-pasos', 'title' => 'Como registrar tu primer cliente', 'slug' => 'primer-cliente', 'sort_order' => 2, 'content' => <<<'MD'
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

## Pro tip:
Marca la **prioridad** como Alta si el cliente necesita atencion inmediata. Esto lo pondra hasta arriba en la lista.
MD],

            // Clientes y Leads
            ['category' => 'clientes-leads', 'title' => 'Como usar las temperaturas de leads', 'slug' => 'temperaturas-leads', 'sort_order' => 1, 'content' => <<<'MD'
# Como usar las temperaturas de leads

Las temperaturas te ayudan a priorizar tu tiempo:

| Temperatura | Significado | Accion recomendada |
|-------------|-------------|-------------------|
| **Caliente** 🔴 | Listo para cerrar, urgente | Contactar HOY, agendar cita |
| **Tibio** 🟡 | Interesado pero no urgente | Seguimiento cada 2-3 dias |
| **Frio** 🔵 | Explorando, largo plazo | Nutrir con contenido, revisitar mensual |

## Cuando cambiar la temperatura:

- **Frio → Tibio**: Respondio un email, pidio mas informacion
- **Tibio → Caliente**: Agenda visita, da presupuesto, muestra urgencia
- **Caliente → Tibio**: Dejo de responder, se enfrio

## Automatizacion:
El sistema puede cambiar temperaturas automaticamente basandose en el **lead scoring** — si un lead abre muchos correos y agenda visitas, sube de temperatura.
MD],

            ['category' => 'clientes-leads', 'title' => 'Lead Scoring: que es y como funciona', 'slug' => 'lead-scoring', 'sort_order' => 2, 'content' => <<<'MD'
# Lead Scoring: que es y como funciona

El **lead scoring** asigna puntos automaticamente a cada cliente basado en sus acciones:

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

- **A (80+)** — Listo para cerrar. Requiere atencion inmediata.
- **B (50-79)** — Interesado activo. Buen momento para empujar.
- **C (20-49)** — Tibio. Necesita mas nutricion.
- **D (0-19)** — Frio o nuevo. Automatizar seguimiento.

## Que hacer con cada grado:

- **Grado A**: Llamar, agendar cita, crear operacion
- **Grado B**: Enviar propiedades personalizadas, invitar a visitar
- **Grado C**: Seguir nutriendo con campanas automaticas
- **Grado D**: Incluir en flujo de bienvenida automatico
MD],

            // Marketing
            ['category' => 'marketing', 'title' => 'Como crear una campana efectiva', 'slug' => 'crear-campana', 'sort_order' => 1, 'content' => <<<'MD'
# Como crear una campana efectiva

## 1. Define tu objetivo
Antes de crear la campana, preguntate:
- Quiero **captar propiedades** nuevas?
- Quiero **nutrir leads frios** para reactivarlos?
- Quiero **cerrar leads calientes**?

## 2. Crea un segmento
Ve a **Marketing > Segmentos** y crea un segmento que agrupe a los clientes objetivo.

Ejemplo para captar propietarios:
- Tipo de interes = "Venta"
- Ciudad = "Ciudad de Mexico"
- Sin operacion activa

## 3. Crea la automatizacion
Ve a **Marketing > Automatizaciones** y crea un flujo:

1. **Trigger**: Entra al segmento "Propietarios CDMX"
2. **Email**: "Tenemos compradores interesados en tu zona"
3. **Esperar**: 3 dias
4. **Condicion**: Abrio el email?
   - Si → **WhatsApp**: "Hola {{nombre}}, vi que leiste mi correo. Te gustaria una valuacion gratuita?"
   - No → **Email**: Reenviar con otro asunto
5. **Esperar**: 5 dias
6. **Condicion**: Score > 50?
   - Si → **Mover a Pipeline**: Captacion, etapa Lead
   - No → **Tarea**: "Llamar a {{nombre}} para seguimiento"

## Mejores practicas:
- **No satures** — maximo 2-3 emails por semana
- **Personaliza** — usa {{nombre}} y datos reales
- **Mide** — revisa tasas de apertura y respuesta
MD],

            // Automatizaciones
            ['category' => 'automatizaciones', 'title' => 'Como funcionan las automatizaciones', 'slug' => 'como-funcionan-automatizaciones', 'sort_order' => 1, 'content' => <<<'MD'
# Como funcionan las automatizaciones

Las automatizaciones ejecutan acciones automaticamente cuando se cumple un **trigger** (disparador).

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
- **Sumar Puntos** — Agrega score manualmente
- **Actualizar Campo** — Cambia temperatura, prioridad, etc.

## Ejemplo real: Nutricion de lead frio

1. Trigger: Nuevo cliente con temperatura "frio"
2. Esperar 1 dia
3. Email: "Bienvenido a Home del Valle — estas son las propiedades mas buscadas"
4. Esperar 3 dias
5. Condicion: Abrio email?
6. Si → WhatsApp: "Hola {{nombre}}, te intereso alguna propiedad?"
7. No → Email: "No te pierdas esta oportunidad en {{ciudad}}"
8. Esperar 7 dias
9. Sumar 10 puntos si seguimos en el flujo
10. Tarea: "Llamar a {{nombre}}"
MD],

            // Operaciones
            ['category' => 'operaciones', 'title' => 'Como usar el pipeline de operaciones', 'slug' => 'pipeline-operaciones', 'sort_order' => 1, 'content' => <<<'MD'
# Como usar el pipeline de operaciones

El pipeline es donde **cierras negocios**. Cada operacion pasa por etapas hasta completarse.

## Tipos de operacion:

### Captacion (captar propiedad en exclusiva)
Lead → Contacto → Visita → Revision docs → Avaluo → Mejoras → Exclusiva → Fotos/video → Carpeta lista

### Venta
Lead → Contacto → Visita → Exclusiva → Publicacion → Busqueda → Investigacion → Contrato → Entrega → Cierre

### Renta
Igual que venta + Activo → Renovacion

## Como crear una operacion:

1. Desde un **cliente**, haz clic en "Nueva operacion"
2. Selecciona el tipo (captacion/venta/renta)
3. Asigna la propiedad y el asesor responsable
4. La operacion empieza en etapa "Lead"

## Moviendo etapas:
- En la vista de operacion, ve el progreso visual
- Haz clic en "Avanzar" para mover a la siguiente etapa
- Cada etapa tiene un **checklist** de tareas recomendadas

## Pro tip:
Las automatizaciones pueden **crear operaciones automaticamente** cuando un lead alcanza cierto score. Asi nunca se te escapa un lead caliente.
MD],

            // Configuracion
            ['category' => 'configuracion', 'title' => 'Como configurar el correo SMTP', 'slug' => 'configurar-smtp', 'sort_order' => 1, 'content' => <<<'MD'
# Como configurar el correo SMTP

Para enviar correos a clientes necesitas configurar tu correo de empresa.

## Configuracion global (admin):
1. Ve a **Configuracion > Email**
2. Ingresa los datos de tu servidor SMTP:
   - Servidor: smtp.tudominio.com
   - Puerto: 587 (TLS) o 465 (SSL)
   - Usuario: tu@homedelvalle.mx
   - Contrasena: tu contrasena
3. Haz clic en **Probar conexion** para verificar

## Configuracion por usuario:
1. Ve a **Mi Perfil**
2. En la pestana "Correo Empresa" ingresa tu correo @homedelvalle.mx
3. Activa el correo

## Importante:
- Cada asesor puede tener su propio correo configurado
- Si un asesor no tiene correo propio, se usa el global
- Los correos de automatizaciones usan la configuracion del administrador
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

    private function seedHelpTips(): void
    {
        $tips = [
            ['context' => 'clients.create', 'title' => 'Datos clave', 'content' => 'Asegurate de capturar el telefono/WhatsApp y la temperatura del lead. Sin telefono no puedes dar seguimiento efectivo.', 'type' => 'tip'],
            ['context' => 'clients.create', 'title' => 'Tipo de interes', 'content' => 'Si el cliente es propietario que quiere vender, selecciona "Venta". Si busca comprar, tambien es "Compra". Un cliente puede tener ambos intereses.', 'type' => 'pro_tip'],
            ['context' => 'clients.index', 'title' => 'Filtra por temperatura', 'content' => 'Enfoca tu dia en leads calientes primero, luego tibios. Los frios dejaselos a las automatizaciones.', 'type' => 'pro_tip'],
            ['context' => 'properties.create', 'title' => 'Fotos profesionales', 'content' => 'Las propiedades con 10+ fotos profesionales se venden 3x mas rapido. Invierte en un fotografo.', 'type' => 'tip'],
            ['context' => 'properties.create', 'title' => 'Propietario', 'content' => 'Asigna al propietario de la propiedad para mantener la trazabilidad completa en el CRM.', 'type' => 'tip'],
            ['context' => 'automations.create', 'title' => 'Empieza simple', 'content' => 'Tu primera automatizacion deberia ser: Nuevo cliente → Esperar 1 dia → Email de bienvenida. Despues la vas complejizando.', 'type' => 'tip'],
            ['context' => 'automations.create', 'title' => 'Delays importan', 'content' => 'No envies todo el mismo dia. Espacia los mensajes: 1 dia, 3 dias, 7 dias. Respetar el tiempo del lead aumenta la conversion.', 'type' => 'warning'],
            ['context' => 'segments.create', 'title' => 'Segmentos efectivos', 'content' => 'Los mejores segmentos combinan 2-3 criterios. Ejemplo: "Caliente + CDMX + Sin operacion" = leads que necesitan atencion YA.', 'type' => 'pro_tip'],
            ['context' => 'operations.create', 'title' => 'Checklist de cada etapa', 'content' => 'Cada etapa tiene un checklist de tareas. Completalas todas antes de avanzar para no dejar cabos sueltos.', 'type' => 'tip'],
            ['context' => 'marketing.dashboard', 'title' => 'ROI real', 'content' => 'Revisa el costo de adquisicion (CAC) por canal. Si Facebook cuesta $500/lead pero cierra al 2%, y referidos cuestan $0 y cierran al 15%, enfoca ahi.', 'type' => 'pro_tip'],
        ];

        foreach ($tips as $t) {
            HelpTip::updateOrCreate(
                ['context' => $t['context'], 'title' => $t['title']],
                $t
            );
        }
    }
}
