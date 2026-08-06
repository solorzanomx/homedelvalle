<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\Property;

/**
 * Inmuebles24 no tiene API — los leads llegan por correo cada vez que
 * alguien consulta el WhatsApp/formulario de un aviso publicado. Este
 * parser convierte ese HTML (siempre la misma plantilla de Navent) en un
 * FormSubmission, igual que SyncEasyBrokerLeads hace con la API de EB.
 *
 * form_type 'inmuebles24' — sin acuse automatico (Alejandro decidio 2026-08-06
 * que el contacto real es por WhatsApp, no por correo, igual que EasyBroker).
 */
class Inmuebles24LeadImporter
{
    public const FROM_DOMAIN = 'usuarios.inmuebles24.com';

    public function looksLikeInmuebles24Lead(?string $fromAddress): bool
    {
        return $fromAddress && str_ends_with(strtolower($fromAddress), '@' . self::FROM_DOMAIN);
    }

    /**
     * @return array|null  null si no se pudo extraer lo minimo (nombre + email o telefono)
     */
    public function parse(string $subject, string $html): ?array
    {
        $text = $this->normalizeHtml($html);

        $nombre   = $this->extractAfterLabel($text, 'Nombre y apellido:');
        $email    = $this->extractEmail($text);
        $telefono = $this->extractPhone($text);

        if (!$nombre && !$email && !$telefono) {
            return null;
        }

        $precio      = $this->extractAfterLabel($text, 'MN ') ? 'MN ' . $this->extractAfterLabel($text, 'MN ') : null;
        $ubicacion   = $this->extractPropertyLocation($text);
        $tipoOp      = str_contains($text, '>Venta<') ? 'Venta' : (str_contains($text, '>Renta<') ? 'Renta' : null);
        $tipoProp    = $this->extractPropertyType($text);
        $codigoAviso = $this->extractAfterLabel($text, 'Código de aviso:');
        $codigoAnun  = $this->extractAfterLabel($text, 'Código del anunciante:');

        // Bloque "Conoce lo que busca [Nombre]" — solo aparece si Inmuebles24
        // tiene un perfil de busqueda guardado del interesado (no siempre viene).
        $buscaTipo = $this->extractAfterIcon($text, 'lupa.png');
        $buscaPresupuesto = $this->extractAfterIcon($text, 'dinero.png');
        $buscaZonas = $this->extractZonasInteres($text);

        preg_match('/REF:#(\d+)#/', $subject, $refMatch);
        preg_match('/CÓD:([A-Z0-9]+)/u', $subject, $codMatch);

        // El asunto trae el titulo del aviso entre "aviso " y " ...!" o "!"
        preg_match('/aviso\s+(.+?)\s*(?:\.\.\.)?!/u', $subject, $tituloMatch);

        return [
            'nombre'          => $nombre ?: 'Sin nombre (Inmuebles24)',
            'email'           => $email,
            'telefono'        => $telefono,
            'precio'          => $precio,
            'ubicacion'       => $ubicacion,
            'tipo_operacion'  => $tipoOp,
            'tipo_propiedad'  => $tipoProp,
            'titulo_aviso'    => $tituloMatch[1] ?? null,
            'codigo_aviso'    => $codigoAviso,
            'codigo_anunciante' => $codigoAnun,
            'ref'             => $refMatch[1] ?? null,
            'busca_tipo'         => $buscaTipo,
            'busca_presupuesto'  => $buscaPresupuesto,
            'busca_zonas'        => $buscaZonas,
        ];
    }

    public function alreadyImported(?string $ref, ?string $email): bool
    {
        if (!$ref) {
            return false;
        }

        return FormSubmission::where('form_type', 'inmuebles24')
            ->where('payload', 'like', '%"ref":"' . $ref . '"%')
            ->exists();
    }

    public function import(array $data): FormSubmission
    {
        // Inmuebles24 no manda mensaje de texto libre en esta notificacion
        // (solo metadata del aviso) — sin mensaje, la IA no tiene nada que
        // analizar y por defecto marcaria "otro"/frio. Pero consultar
        // activamente el WhatsApp de un aviso YA es una senal de intencion
        // real, distinta de solo ver el anuncio — se trata como caliente
        // por heuristica directa, y el rol se deriva del tipo de operacion
        // (venta -> comprador, renta -> inquilino) en vez de la IA.
        $clientType = str_contains(mb_strtolower($data['tipo_operacion'] ?? ''), 'renta') ? 'renter' : 'buyer';
        $temperatura = 'hot';
        [$budgetMin, $budgetMax] = $this->parseBudgetRange($data['busca_presupuesto'] ?? null);

        // Vincula al aviso local si el codigo coincide con una Property que
        // Alejandro ya anoto a mano (Inmuebles24 no tiene API para hacerlo
        // solo, a diferencia de easybroker_id) — misma convencion de payload
        // ('propiedad_local_id') que usa SyncEasyBrokerLeads para que la
        // ficha del lead muestre el bloque "Propiedad de interés" real.
        $propiedadLocal = !empty($data['codigo_aviso'])
            ? Property::where('inmuebles24_ad_code', $data['codigo_aviso'])->first()
            : null;

        // withoutEvents: igual que SyncEasyBrokerLeads — Alejandro decidio
        // 2026-08-06 que NO se manda acuse automatico por correo (el contacto
        // real con estos leads es por WhatsApp), asi que no se dispara
        // FormSubmitted (SendAcuseMail/NotifyAdminsNewLead/etc.).
        return FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type'        => 'inmuebles24',
            'source_page'      => 'inmuebles24:' . ($data['codigo_aviso'] ?? 'sin-codigo'),
            'full_name'        => $data['nombre'],
            'email'            => $data['email'] ?: 'i24-' . ($data['ref'] ?? uniqid()) . '@sin-correo.inmuebles24',
            'phone'            => $data['telefono'] ?: 'sin teléfono',
            'lead_tag'         => 'LEAD_INMUEBLES24',
            'client_type'      => $clientType,
            'lead_temperature' => $temperatura,
            'status'           => 'new',
            'utm_source'       => 'inmuebles24',
            'utm_medium'       => 'email_lead',
            'budget_min'       => $budgetMin,
            'budget_max'       => $budgetMax,
            'property_type'    => $data['busca_tipo'] ?? $data['tipo_propiedad'] ?? null,
            'payload'          => [
                'ref'                => $data['ref'],
                'codigo_aviso'       => $data['codigo_aviso'],
                'codigo_anunciante'  => $data['codigo_anunciante'],
                'titulo_aviso'       => $data['titulo_aviso'],
                'tipo_operacion'     => $data['tipo_operacion'],
                'tipo_propiedad'     => $data['tipo_propiedad'],
                'precio'             => $data['precio'],
                'ubicacion'          => $data['ubicacion'],
                'busca_tipo'         => $data['busca_tipo'] ?? null,
                'busca_presupuesto'  => $data['busca_presupuesto'] ?? null,
                'busca_zonas'        => $data['busca_zonas'] ?? [],
                'propiedad_local_id' => $propiedadLocal?->id,
                'propiedad_local'    => $propiedadLocal?->title,
            ],
        ]));
    }

    private function normalizeHtml(string $html): string
    {
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_replace('/\s+/', ' ', $decoded) ?? $decoded;
    }

    private function extractAfterLabel(string $text, string $label): ?string
    {
        $label = preg_quote($label, '/');
        if (!preg_match('/' . $label . '\s*(?:<[^>]*>\s*)*([^<]+)/u', $text, $m)) {
            return null;
        }
        $value = trim($m[1]);
        return $value !== '' ? $value : null;
    }

    private function extractEmail(string $text): ?string
    {
        return preg_match('/mailto:([^"\']+)/', $text, $m) ? trim($m[1]) : null;
    }

    private function extractPhone(string $text): ?string
    {
        if (!preg_match('/Tel[ée]fono:\s*(?:<[^>]*>\s*)*(\d{10,13})/u', $text, $m)) {
            return null;
        }
        // Los correos de Inmuebles24 anteponen el codigo de pais (52) — nos
        // quedamos con los ultimos 10 digitos, formato nacional usable en CRM/WhatsApp.
        $digits = preg_replace('/\D/', '', $m[1]);
        return substr($digits, -10);
    }

    private function extractPropertyType(string $text): ?string
    {
        foreach (['Departamento', 'Casa', 'Terreno', 'Oficina', 'Local', 'Bodega', 'Edificio'] as $tipo) {
            if (str_contains($text, '>' . $tipo . '<')) {
                return $tipo;
            }
        }
        return null;
    }

    private function extractPropertyLocation(string $text): ?string
    {
        // La colonia/ubicacion vive en el span de descripcion junto al precio,
        // con la forma "...color:#7C98A7;...">Colonia, Alcaldía</span>
        if (preg_match('/color:#7C98A7[^>]*>([^<]{4,80})<\/span>/u', $text, $m)) {
            $value = trim($m[1]);
            if ($value !== '' && !str_contains($value, 'MN') && !str_contains($value, 'Mantenimiento')) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Bloque "Conoce lo que busca [Nombre]": cada renglon es un icono
     * (lupa.png = tipo de propiedad/operacion, dinero.png = presupuesto)
     * seguido del primer <span>texto</span>.
     */
    private function extractAfterIcon(string $text, string $iconFile): ?string
    {
        $icon = preg_quote($iconFile, '/');
        if (!preg_match('/' . $icon . '"[^>]*>\s*<span[^>]*>([^<]+)<\/span>/u', $text, $m)) {
            return null;
        }
        $value = trim($m[1]);
        return $value !== '' ? $value : null;
    }

    /**
     * Zonas de interes: badges con un estilo especifico y distinto del badge
     * "Venta | Departamento" del aviso consultado (ese usa border-radius:23px,
     * las zonas usan border-radius:4px con este padding exacto).
     */
    private function extractZonasInteres(string $text): array
    {
        preg_match_all(
            '/border:\s*1px solid #EDEDED;border-radius:4px;padding:2px 8px 2px 8px;margin-bottom:4px;margin-top:8px;">([^<]+)<\/span>/u',
            $text,
            $matches
        );

        return array_values(array_unique(array_map('trim', $matches[1] ?? [])));
    }

    /**
     * "MXN 4.890.000 - MXN 5.190.000" -> [4890000, 5190000]. Formato Navent
     * usa punto como separador de miles (no decimal). Un solo monto tambien
     * se acepta (min=max). Sin match, [null, null].
     */
    private function parseBudgetRange(?string $raw): array
    {
        if (!$raw) {
            return [null, null];
        }

        preg_match_all('/[\d.]{4,}/', $raw, $matches);
        $numbers = array_map(fn ($n) => (int) str_replace('.', '', $n), $matches[0] ?? []);

        if (empty($numbers)) {
            return [null, null];
        }

        return count($numbers) >= 2
            ? [min($numbers[0], $numbers[1]), max($numbers[0], $numbers[1])]
            : [$numbers[0], $numbers[0]];
    }
}
