<?php
namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\AcuseData;
use App\Models\AcuseEmailConfig;
use App\Models\MarketColonia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcuseMail extends Mailable {
    use Queueable, SerializesModels;

    public function __construct(private readonly AcuseData $data) {}

    public function envelope(): Envelope {
        $config = AcuseEmailConfig::forType($this->data->form_type);
        $subject = $this->interpolate($config->subject, $this->buildVars());
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject
        );
    }

    public function content(): Content {
        $config  = AcuseEmailConfig::forType($this->data->form_type);
        $vars    = $this->buildVars();
        $folio   = 'HDV-' . strtoupper(substr(md5($this->data->folio), 0, 4)) . '-' . $this->data->folio;
        $iconBase = rtrim(asset('img/email'), '/') . '/';

        return new Content(
            view: 'emails.v4.acuse',
            with: [
                'config'    => $config,
                'vars'      => $vars,
                'folio'     => $folio,
                'logoUrl'   => $this->getLogoUrl(),
                'iconBase'  => $iconBase,
                'cta1Url'   => $this->resolveCta($config->cta1_type, $config->cta1_url_static, $vars),
                'cta2Url'   => $config->cta2_type ? $this->resolveCta($config->cta2_type, $config->cta2_url_static, $vars) : null,
            ]
        );
    }

    private function buildVars(): array {
        $p = $this->data->payload;
        $nombre = $this->data->nombre;
        $primerNombre = $nombre ? explode(' ', trim($nombre))[0] : '';
        $saludo = $primerNombre ? ', ' . $primerNombre : '';

        // Mascotas texto para arrendatario
        $mascotas = $p['mascotas'] ?? 'no';
        $mascotasTexto = match($mascotas) {
            'perro', 'gato', 'otra' => 'Incluiremos opciones pet-friendly en tu selección.',
            default => '',
        };

        // Zonas
        $zonas = $p['zonas'] ?? [];
        $zonasStr = is_array($zonas) ? implode(', ', array_slice($zonas, 0, 3)) : ($zonas ?: '');

        // Tipo inmueble
        $tipoInmueble = $p['tipo_inmueble'] ?? $p['tipo_propiedad'] ?? '';
        if (is_array($tipoInmueble)) $tipoInmueble = implode(', ', $tipoInmueble);
        $tipoLabels = [
            'departamento' => 'departamento', 'casa' => 'casa', 'estudio' => 'estudio',
            'loft' => 'loft', 'oficina' => 'oficina', 'casa_jardin' => 'casa con jardín',
            'terreno' => 'terreno', 'comercial' => 'local comercial',
        ];
        $tipoInmuebleLabel = $tipoLabels[$tipoInmueble] ?? $tipoInmueble;

        return [
            'nombre'         => $nombre,
            'primer_nombre'  => $primerNombre,
            'saludo'         => $saludo, // ", Juan" or ""
            'colonia'        => $p['colonia'] ?? '',
            'tipo_propiedad' => $tipoLabels[$p['tipo_propiedad'] ?? ''] ?? ($p['tipo_propiedad'] ?? ''),
            'tipo_inmueble'  => $tipoInmuebleLabel,
            'zonas'          => $zonasStr,
            'presupuesto'    => $p['presupuesto'] ?? $p['renta_mensual'] ?? '',
            'recamaras'      => $p['recamaras'] ?? '',
            'timing'         => $p['timing'] ?? '',
            'mascotas_texto' => $mascotasTexto,
        ];
    }

    private function interpolate(string $text, array $vars): string {
        foreach ($vars as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        // Special: {{nombre}} in titulo should be ", Nombre" (saludo)
        $text = str_replace('{{nombre}}', $vars['saludo'] ?? '', $text);
        return $text;
    }

    private function resolveCta(string $type, ?string $staticUrl, array $vars): string {
        return match($type) {
            'precios_colonia'      => $this->resolveColoniaUrl($vars['colonia'] ?? '', 'sale'),
            'precios_colonia_renta'=> $this->resolveColoniaUrl($vars['colonia'] ?? '', 'rent'),
            'propiedades_renta'    => url('/propiedades') . '?operation_type=rental',
            'propiedades_compra'   => url('/propiedades'),
            'precios'              => url('/precios'),
            default                => $staticUrl ?? url('/propiedades'),
        };
    }

    private function resolveColoniaUrl(string $coloniaName, string $operationType = 'sale'): string {
        if (!$coloniaName) return url('/precios');
        try {
            $colonia = MarketColonia::where('is_published', true)
                ->where(function($q) use ($coloniaName) {
                    $q->where('name', 'LIKE', '%' . $coloniaName . '%')
                      ->orWhere('slug', 'LIKE', '%' . \Str::slug($coloniaName) . '%');
                })
                ->with('zone')
                ->first();
            if ($colonia && $colonia->zone?->slug && $colonia->slug) {
                return url('/precios/' . $colonia->zone->slug . '/' . $colonia->slug);
            }
        } catch (\Throwable) {}
        return url('/precios');
    }

    private function getLogoUrl(): ?string {
        try {
            $settings = \App\Models\SiteSetting::current();
            if ($settings?->logo_path) {
                return url(\Illuminate\Support\Facades\Storage::url($settings->logo_path));
            }
        } catch (\Throwable) {}
        return null;
    }
}
