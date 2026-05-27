<?php

namespace Database\Seeders;

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class PresentationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'intent_target' => 'general',
                'name'          => 'Presentación Inicial — General',
                'proposicion'   => 'Somos expertos en el mercado inmobiliario de la Ciudad de México y estamos listos para acompañarte en el proceso de comercialización de tu inmueble con la atención personalizada que te mereces.',
                'enfoque'       => 'Analizaremos las mejores opciones para tu inmueble — ya sea venta o renta — y te presentaremos una estrategia comercial a la medida de tus objetivos y tiempos.',
                'marketing_default' => "• Fotografía profesional y video recorrido del inmueble\n• Ficha editorial boutique con descripción curada\n• Distribución segmentada a nuestra red de contactos activos\n• Difusión en portales seleccionados y redes especializadas\n• Seguimiento semanal con reporte de actividad",
            ],
            [
                'intent_target' => 'venta_constructor',
                'name'          => 'Presentación Inicial — Venta a Constructor',
                'proposicion'   => 'Tu inmueble tiene un potencial de desarrollo que merece llegar a los compradores correctos. Contamos con una red activa de desarrolladores y constructores que buscan exactamente lo que tú ofreces.',
                'enfoque'       => 'Nos especializamos en conectar propietarios con desarrolladores calificados. Conocemos el potencial de zonificación H5/H6 en Benito Juárez y sabemos cómo presentar tu terreno o inmueble para maximizar el precio de adquisición.',
                'marketing_default' => "• Análisis de potencial de desarrollo y zonificación aplicable\n• Brief técnico con densidades, CUS, COS y restricciones\n• Presentación directa a nuestra red de 30+ desarrolladores activos en BJ\n• Negociación estructurada para maximizar el precio por m²\n• Acompañamiento en due diligence técnico y legal",
            ],
            [
                'intent_target' => 'venta_residencial',
                'name'          => 'Presentación Inicial — Venta Residencial',
                'proposicion'   => 'Vender tu inmueble es una decisión importante. Con Home del Valle, tienes a tu lado un equipo boutique que se dedica a cada caso con la atención que merece: pocos inmuebles, más control, mejores resultados.',
                'enfoque'       => 'Nuestro proceso de venta está diseñado para encontrar al comprador final calificado —no al primero que llama, sino al que mejor encaja— y cerrar al precio correcto en el tiempo correcto.',
                'marketing_default' => "• Fotografía profesional y video tour inmersivo\n• Ficha editorial con descripción de lifestyle del inmueble\n• Pre-filtro de compradores para mostrar solo a perfiles calificados\n• Acceso al Observatorio de Precios hdv para fijar el precio óptimo\n• Reportes de actividad semanales con métricas reales",
            ],
            [
                'intent_target' => 'venta_comercial',
                'name'          => 'Presentación Inicial — Venta Comercial',
                'proposicion'   => 'El mercado de inmuebles comerciales requiere un perfil de comprador muy específico: el inversionista que entiende la rentabilidad a largo plazo. En Home del Valle sabemos dónde encontrarlos.',
                'enfoque'       => 'Presentamos tu inmueble comercial con los indicadores que los inversionistas necesitan ver: cap rate, flujo esperado, potencial de plusvalía y blindaje legal. Así se cierra más rápido y al precio justo.',
                'marketing_default' => "• Análisis de rentabilidad y cap rate del inmueble\n• Presentación a red de inversionistas y family offices activos\n• Valoración comparativa con transacciones recientes de la zona\n• Due diligence legal completo antes de la firma\n• Gestión completa hasta escrituración",
            ],
            [
                'intent_target' => 'renta_residencial',
                'name'          => 'Presentación Inicial — Renta Residencial',
                'proposicion'   => 'Rentar tu inmueble no es solo colocar a alguien que pague. Es encontrar al inquilino correcto, protegerte legalmente y tener un socio de confianza que administre la relación por ti.',
                'enfoque'       => 'Nuestro proceso de renta incluye calificación seria de candidatos (buro, ingresos comprobables, referencias), póliza jurídica sin costo adicional para el propietario y acompañamiento durante toda la vigencia del contrato.',
                'marketing_default' => "• Calificación exhaustiva de candidatos (buró, comprobante, referencias)\n• Póliza jurídica que cubre hasta 18 meses de renta en caso de incumplimiento\n• Contrato de arrendamiento con cláusulas de protección al propietario\n• Administración de cobro mensual y reporte de pagos\n• Inspección de entrega y devolución documentada",
            ],
            [
                'intent_target' => 'renta_comercial',
                'name'          => 'Presentación Inicial — Renta Comercial',
                'proposicion'   => 'El arrendamiento comercial tiene sus propias reglas. Plazos más largos, ajustes anuales, garantías corporativas y cláusulas específicas según el giro del negocio. Somos expertos en esto.',
                'enfoque'       => 'Manejamos arrendamientos comerciales con contratos de 3 a 10 años, ajustes pactados (INPC + spread), garantía corporativa o fianza, y cláusulas de uso específico que protegen tu inmueble durante toda la vigencia.',
                'marketing_default' => "• Búsqueda segmentada de arrendatario según giro y perfil de riesgo\n• Contrato comercial con ajustes anuales y penalidades claras\n• Garantía corporativa o fianza afianzadora de primer nivel\n• Revisión de uso permitido y restricciones de operación\n• Seguimiento durante toda la vigencia del contrato",
            ],
        ];

        foreach ($templates as $data) {
            ContractTemplate::updateOrCreate(
                [
                    'type'          => 'presentation',
                    'intent_target' => $data['intent_target'],
                ],
                [
                    'name'      => $data['name'],
                    'is_active' => true,
                    'variables' => [
                        '{{NombrePropietario}}' => 'Nombre del propietario',
                        '{{InmuebleTipo}}'      => 'Tipo de inmueble',
                        '{{InmuebleColonia}}'   => 'Colonia del inmueble',
                        '{{ComisionPct}}'       => 'Porcentaje de comisión',
                        '{{PrecioSugerido}}'    => 'Precio sugerido de venta o renta',
                        '{{PlanMarketing}}'     => 'Plan de marketing editable',
                        '{{NombreAgente}}'      => 'Nombre del agente HDV',
                        '{{TelefonoAgente}}'    => 'Teléfono del agente HDV',
                        '{{EmailAgente}}'       => 'Email del agente HDV',
                        '{{FechaPresentacion}}' => 'Fecha de la presentación',
                        '{{LogoUrl}}'           => 'URL del logo de HDV',
                        '{{SloganHDV}}'         => 'Slogan de Home del Valle',
                        '{{PhotoUrl}}'          => 'URL de la foto principal del inmueble (opcional)',
                    ],
                    'body' => $this->buildTemplateBody($data),
                ]
            );
        }
    }

    private function buildTemplateBody(array $data): string
    {
        $marketing = nl2br(htmlspecialchars($data['marketing_default']));

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  @import url('https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap');
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Inter', Arial, sans-serif; color: #1e293b; background: #fff; font-size: 14px; line-height: 1.6; }
  .page { width: 816px; min-height: 1056px; margin: 0 auto; padding: 0; page-break-after: always; position: relative; }
  .page:last-child { page-break-after: auto; }
  /* Portada */
  .cover { background: #1e1b4b; color: #fff; display: flex; flex-direction: column; justify-content: space-between; padding: 60px 56px; min-height: 1056px; }
  .cover-logo { font-size: 13px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,.6); }
  .cover-main { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 40px 0; }
  .cover-tag { display: inline-block; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); border-radius: 4px; padding: 4px 12px; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
  .cover-title { font-size: 36px; font-weight: 700; line-height: 1.2; margin-bottom: 12px; }
  .cover-subtitle { font-size: 18px; font-weight: 300; color: rgba(255,255,255,.8); margin-bottom: 40px; }
  .cover-property { border-top: 1px solid rgba(255,255,255,.2); padding-top: 32px; }
  .cover-property-label { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: 8px; }
  .cover-property-name { font-size: 22px; font-weight: 600; }
  .cover-footer { display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid rgba(255,255,255,.2); padding-top: 24px; font-size: 12px; color: rgba(255,255,255,.6); }
  .cover-photo { width: 100%; height: 280px; object-fit: cover; border-radius: 8px; margin-bottom: 32px; }
  /* Secciones interiores */
  .inner { padding: 56px 56px 40px; }
  .section-tag { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #64748b; margin-bottom: 8px; }
  .section-title { font-size: 26px; font-weight: 700; color: #1e1b4b; margin-bottom: 24px; line-height: 1.2; }
  .section-divider { width: 40px; height: 3px; background: #10b981; margin-bottom: 32px; }
  p { color: #475569; font-size: 14px; line-height: 1.75; margin-bottom: 16px; }
  strong { color: #1e293b; }
  /* Bullets */
  .bullet-list { list-style: none; padding: 0; margin: 16px 0 24px; }
  .bullet-list li { padding: 10px 0 10px 28px; position: relative; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 13.5px; line-height: 1.5; }
  .bullet-list li:last-child { border-bottom: none; }
  .bullet-list li::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 8px; height: 8px; border-radius: 50%; background: #10b981; }
  /* Comisión highlight */
  .commission-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 24px 28px; margin: 24px 0; }
  .commission-pct { font-size: 48px; font-weight: 700; color: #065f46; line-height: 1; }
  .commission-label { font-size: 13px; color: #064e3b; margin-top: 4px; }
  /* Agent card */
  .agent-card { background: #1e1b4b; color: #fff; border-radius: 8px; padding: 24px 28px; margin-top: 24px; display: flex; align-items: center; gap: 20px; }
  .agent-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
  .agent-info p { color: rgba(255,255,255,.75); font-size: 13px; margin: 2px 0; }
  /* Services grid */
  .services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
  .service-item { background: #f8fafc; border-radius: 8px; padding: 18px 20px; }
  .service-item h4 { font-size: 13px; font-weight: 600; color: #1e1b4b; margin-bottom: 6px; }
  .service-item p { font-size: 12px; color: #64748b; margin: 0; }
  /* Footer de página */
  .page-footer { position: absolute; bottom: 24px; left: 56px; right: 56px; border-top: 1px solid #e2e8f0; padding-top: 12px; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; }
  .disclaimer { font-size: 10px; color: #94a3b8; line-height: 1.5; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 32px; }
</style>
</head>
<body>

<!-- Página 1: Portada -->
<div class="page">
  <div class="cover">
    <div class="cover-logo">Home del Valle · Bienes Raíces</div>
    <div class="cover-main">
      <span class="cover-tag">Presentación inicial</span>
      {{#if PhotoUrl}}<img src="{{PhotoUrl}}" class="cover-photo" alt="Inmueble">{{/if}}
      <h1 class="cover-title">Propuesta de comercialización para tu inmueble</h1>
      <p class="cover-subtitle">Preparada especialmente para {{NombrePropietario}}</p>
      <div class="cover-property">
        <div class="cover-property-label">Inmueble</div>
        <div class="cover-property-name">{{InmuebleTipo}} · {{InmuebleColonia}}</div>
      </div>
    </div>
    <div class="cover-footer">
      <span>{{FechaPresentacion}}</span>
      <span>Agente: {{NombreAgente}}</span>
    </div>
  </div>
</div>

<!-- Página 2: Quiénes somos -->
<div class="page">
  <div class="inner">
    <div class="section-tag">Sobre nosotros</div>
    <h2 class="section-title">¿Por qué Home del Valle?</h2>
    <div class="section-divider"></div>
    <p>
      Con más de 30 años de experiencia en el mercado inmobiliario de la Ciudad de México,
      Home del Valle es una agencia <strong>boutique</strong> que opera bajo un principio claro:
      pocos inmuebles, más control, mejores resultados.
    </p>
    <p>
      No somos una agencia de volumen. Seleccionamos cada caso que tomamos y lo trabajamos con
      la dedicación de un equipo completo — desde la dirección general hasta el área legal y
      de marketing. Cada propietario que trabaja con nosotros tiene acceso directo a quienes
      toman las decisiones.
    </p>
    <p>
      Nuestros diferenciadores:
    </p>
    <ul class="bullet-list">
      <li><strong>Dirección involucrada en cada operación</strong> — no delegamos a asesores sin experiencia</li>
      <li><strong>Blindaje legal</strong> con nuestra Dirección General y área jurídica interna</li>
      <li><strong>Observatorio de Precios</strong> propio con datos de la zona en tiempo real</li>
      <li><strong>Red de contactos activos</strong> — compradores, inquilinos y desarrolladores calificados</li>
      <li><strong>Portal del Propietario</strong> con seguimiento transparente en cualquier momento</li>
    </ul>
    <p>
      Estamos ubicados en Heriberto Frías 903-A, Col. del Valle, CDMX — en el corazón del
      mercado que mejor conocemos.
    </p>
  </div>
  <div class="page-footer">
    <span>Home del Valle · {{SloganHDV}}</span>
    <span>{{FechaPresentacion}}</span>
  </div>
</div>

<!-- Página 3: Lo que proponemos -->
<div class="page">
  <div class="inner">
    <div class="section-tag">Nuestra propuesta</div>
    <h2 class="section-title">Lo que proponemos para tu inmueble</h2>
    <div class="section-divider"></div>
    <p>{$data['proposicion']}</p>
    <p>{$data['enfoque']}</p>
    {{#if PrecioSugerido}}
    <div class="commission-box" style="background:#eff6ff;border-color:#bfdbfe;">
      <div style="font-size:13px;color:#1e40af;margin-bottom:4px;">Precio de referencia sugerido</div>
      <div style="font-size:32px;font-weight:700;color:#1e3a8a;">{{PrecioSugerido}}</div>
      <div style="font-size:12px;color:#1e40af;margin-top:4px;">Basado en el Observatorio de Precios HDV para {{InmuebleColonia}}</div>
    </div>
    {{/if}}
  </div>
  <div class="page-footer">
    <span>Home del Valle · {{SloganHDV}}</span>
    <span>{{FechaPresentacion}}</span>
  </div>
</div>

<!-- Página 4: Plan de marketing -->
<div class="page">
  <div class="inner">
    <div class="section-tag">Estrategia comercial</div>
    <h2 class="section-title">Plan de marketing para tu inmueble</h2>
    <div class="section-divider"></div>
    <p>
      Cada inmueble que manejamos recibe un plan de marketing diseñado para alcanzar al
      comprador o inquilino correcto, no al primero que llama.
    </p>
    <div style="background:#f8fafc;border-radius:8px;padding:24px 28px;margin:16px 0;">
      <div style="white-space:pre-line;color:#475569;font-size:13.5px;line-height:1.8;">{{PlanMarketing}}</div>
    </div>
    <p style="margin-top:16px;">
      Toda la actividad se reporta semanalmente y está visible en tu portal de propietario en
      cualquier momento.
    </p>
  </div>
  <div class="page-footer">
    <span>Home del Valle · {{SloganHDV}}</span>
    <span>{{FechaPresentacion}}</span>
  </div>
</div>

<!-- Página 5: Servicios incluidos -->
<div class="page">
  <div class="inner">
    <div class="section-tag">Sin costo adicional</div>
    <h2 class="section-title">Servicios incluidos en nuestra comisión</h2>
    <div class="section-divider"></div>
    <p>
      La comisión de Home del Valle incluye todo lo necesario para que el proceso sea exitoso.
      No hay cargos adicionales sorpresa.
    </p>
    <div class="services-grid">
      <div class="service-item">
        <h4>Valuación profesional</h4>
        <p>Opinión de valor con datos reales del Observatorio de Precios HDV para fijar el precio óptimo.</p>
      </div>
      <div class="service-item">
        <h4>Blindaje legal</h4>
        <p>Revisión y elaboración de contratos por nuestra dirección jurídica interna. Sin costos extras.</p>
      </div>
      <div class="service-item">
        <h4>Gestoría documental</h4>
        <p>Recopilación y verificación de todos los documentos necesarios para el proceso.</p>
      </div>
      <div class="service-item">
        <h4>Asesoría fiscal básica</h4>
        <p>Orientación sobre implicaciones fiscales de la operación (ISR, IVA según aplique).</p>
      </div>
      <div class="service-item">
        <h4>Acompañamiento a notaría</h4>
        <p>Presencia en firma notarial y coordinación con el notario de tu confianza.</p>
      </div>
      <div class="service-item">
        <h4>Portal del Propietario</h4>
        <p>Seguimiento en tiempo real del proceso desde tu celular o computadora.</p>
      </div>
    </div>
  </div>
  <div class="page-footer">
    <span>Home del Valle · {{SloganHDV}}</span>
    <span>{{FechaPresentacion}}</span>
  </div>
</div>

<!-- Página 6: Comisión y próximos pasos -->
<div class="page">
  <div class="inner">
    <div class="section-tag">Propuesta económica</div>
    <h2 class="section-title">Comisión y próximos pasos</h2>
    <div class="section-divider"></div>
    <div class="commission-box">
      <div class="commission-pct">{{ComisionPct}}%</div>
      <div class="commission-label">Comisión de comercialización sobre precio de cierre</div>
    </div>
    <p>
      Esta comisión cubre todos los servicios descritos en esta presentación, desde la
      estrategia de marketing hasta el acompañamiento en la firma. No existen cobros
      adicionales ni anticipos.
    </p>
    <p><strong>¿Cuál es el siguiente paso?</strong></p>
    <ul class="bullet-list">
      <li>Agendar una visita técnica al inmueble esta semana</li>
      <li>Presentar nuestra valuación y confirmar el precio de salida</li>
      <li>Firmar el contrato de exclusiva o de comercialización</li>
      <li>Iniciar la estrategia de marketing inmediatamente</li>
    </ul>
    <div class="agent-card">
      <div class="agent-info">
        <h3>{{NombreAgente}}</h3>
        <p>Agente · Home del Valle Bienes Raíces</p>
        <p>{{TelefonoAgente}}</p>
        <p>{{EmailAgente}}</p>
      </div>
    </div>
    <div class="disclaimer">
      Este documento es informativo y no constituye oferta vinculante. Los términos comerciales
      se formalizan al firmar el contrato de exclusiva o de comercialización con
      Home del Valle Bienes Raíces.
    </div>
  </div>
  <div class="page-footer">
    <span>Home del Valle · {{SloganHDV}}</span>
    <span>{{FechaPresentacion}}</span>
  </div>
</div>

</body>
</html>
HTML;
    }
}
