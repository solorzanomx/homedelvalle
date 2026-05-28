<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketPromptTemplate extends Model
{
    protected $fillable = ['key', 'label', 'description', 'prompt_text', 'default_text'];

    // ─── Acceso ──────────────────────────────────────────────────────────────

    /**
     * Devuelve el prompt activo para la clave dada.
     * Si no existe en BD, usa el default hardcodeado.
     */
    public static function getPrompt(string $key): string
    {
        $row = static::where('key', $key)->first();
        if ($row && !empty($row->prompt_text)) {
            return $row->prompt_text;
        }
        return static::defaults()[$key] ?? '';
    }

    /**
     * Siembra los defaults en BD si no existen.
     * Llamado desde el seeder o desde el admin.
     */
    public static function seedDefaults(): void
    {
        foreach (static::defaults() as $key => $text) {
            static::firstOrCreate(
                ['key' => $key],
                [
                    'label'        => static::labels()[$key] ?? $key,
                    'description'  => static::descriptions()[$key] ?? null,
                    'prompt_text'  => $text,
                    'default_text' => $text,
                ]
            );
        }
    }

    /**
     * Restaura el prompt al texto default original.
     */
    public function resetToDefault(): void
    {
        $this->update(['prompt_text' => $this->default_text]);
    }

    // ─── Metadatos de prompts ─────────────────────────────────────────────────

    public static function labels(): array
    {
        return [
            'sale.search'    => 'Búsqueda de venta (Perplexity)',
            'rent.search'    => 'Búsqueda de renta (Perplexity)',
            'sale.analysis'  => 'Análisis de venta (Claude)',
            'rent.analysis'  => 'Análisis de renta (Claude)',
        ];
    }

    public static function descriptions(): array
    {
        return [
            'sale.search'    => 'Prompt enviado a Perplexity para buscar anuncios de venta en portales inmobiliarios. Variables: {colonia}, {type_label}, {fields}',
            'rent.search'    => 'Prompt enviado a Perplexity para buscar anuncios de renta en portales inmobiliarios. Variables: {colonia}, {type_label}, {fields}',
            'sale.analysis'  => 'Prompt enviado a Claude Haiku para filtrar outliers y calcular estadísticas de precio/m². Variables: {colonia}, {type_label}, {listings}',
            'rent.analysis'  => 'Prompt enviado a Claude Haiku para calcular precio de renta mensual por m². Variables: {colonia}, {type_label}, {listings}, {range_min}, {range_max}, {price_m2_min}, {price_m2_max}, {age_note}, {hierarchy_note}',
        ];
    }

    public static function variables(): array
    {
        return [
            'sale.search'   => ['{colonia}', '{type_label}', '{fields}'],
            'rent.search'   => ['{colonia}', '{type_label}', '{fields}'],
            'sale.analysis' => ['{colonia}', '{type_label}', '{listings}'],
            'rent.analysis' => ['{colonia}', '{type_label}', '{listings}', '{range_min}', '{range_max}', '{price_m2_min}', '{price_m2_max}', '{age_note}', '{hierarchy_note}'],
        ];
    }

    // ─── Prompts default ──────────────────────────────────────────────────────

    public static function defaults(): array
    {
        return [
            'sale.search' => <<<'PROMPT'
Busca anuncios ACTUALES de {type_label} en VENTA en la Colonia {colonia}, Benito Juárez, Ciudad de México, CDMX.

Busca en: Inmuebles24, Lamudi, Vivanuncios, Propiedades.com, MercadoLibre Inmuebles, Metros Cúbicos, y cualquier otro portal inmobiliario disponible.

Si no encuentras suficientes anuncios en "{colonia}" específicamente, incluye anuncios de colonias adyacentes dentro de Benito Juárez.

Para cada anuncio encontrado, extrae:
{fields}
- "fuente": nombre del portal o sitio web

Devuelve entre 6 y 15 anuncios reales que hayas encontrado.

Responde ÚNICAMENTE con un JSON array, sin texto adicional ni markdown:
[
  {"precio": 3800000, "m2": 78, "antiguedad": 12, "recamaras": 2, "piso": 3, "fuente": "Inmuebles24"},
  {"precio": 4200000, "m2": 95, "antiguedad": 25, "recamaras": 3, "piso": 1, "fuente": "Lamudi"}
]

Si encuentras menos de 2 anuncios en total, responde: {"error": "sin_datos"}
PROMPT,

            'rent.search' => <<<'PROMPT'
Busca anuncios ACTUALES de {type_label} en RENTA (arrendamiento) en la Colonia {colonia}, Benito Juárez, Ciudad de México, CDMX.

Busca en: Inmuebles24, Lamudi, Vivanuncios, Propiedades.com, MercadoLibre Inmuebles, Metros Cúbicos, easybroker, y cualquier otro portal inmobiliario disponible.

Si no encuentras suficientes anuncios en "{colonia}" específicamente, incluye anuncios de colonias adyacentes dentro de Benito Juárez.

Para cada anuncio encontrado, extrae:
{fields}
- "fuente": nombre del portal o sitio web

Devuelve entre 6 y 15 anuncios reales que hayas encontrado.

Responde ÚNICAMENTE con un JSON array, sin texto adicional ni markdown:
[
  {"precio_renta": 18000, "m2": 75, "antiguedad": 10, "recamaras": 2, "fuente": "Inmuebles24"}
]

Si encuentras menos de 2 anuncios en total, responde: {"error": "sin_datos"}
PROMPT,

            'sale.analysis' => <<<'PROMPT'
Eres un analista de mercado inmobiliario con experiencia en estadística de bienes raíces en México.
Tu tarea: procesar listings crudos y producir estadísticas de precio por m² confiables.

Se te proporcionan listings de {type_label} en VENTA en la Colonia {colonia}, Benito Juárez, CDMX:

LISTINGS:
{listings}

INSTRUCCIONES:

1. VALIDACIÓN — Descarta listings con:
   - precio < 500,000 o > 50,000,000 MXN
   - m2 < 20 o m2 > 1000
   - sin precio o sin m2

2. PRECIO/M² — Para cada listing válido: precio_m2 = precio / m2
   Rango razonable en Benito Juárez: $30,000–$180,000 MXN/m²

3. OUTLIERS — Excluye listings donde precio_m2 < (Q1 - 1.5×IQR) o > (Q3 + 1.5×IQR)

4. CLASIFICACIÓN POR ANTIGÜEDAD
   - "new": 0–10 años | "mid": 11–30 años | "old": más de 30 años
   - Sin antigüedad: asignar a "mid"
   - Omite categorías con < 2 listings válidos

5. ESTADÍSTICAS (por categoría con ≥ 2 listings)
   - low = P25, avg = mediana, high = P75 del precio_m2
   - Redondear a entero

6. JERARQUÍA — En BJ: nuevo > seminuevo > antiguo en precio/m²
   Si se viola: omite esa categoría

7. CONFIANZA: "high" ≥5 listings, "medium" 2–4, "low" <2

Responde ÚNICAMENTE con este JSON exacto, sin markdown:
{
  "prices": {
    "new": {"low": 75000, "avg": 82000, "high": 90000},
    "mid": {"low": 65000, "avg": 72000, "high": 80000},
    "old": {"low": 52000, "avg": 58000, "high": 65000}
  },
  "confidence": "high",
  "listings_analyzed": 10,
  "outliers_excluded": 2,
  "price_m2_range": {"min": 62000, "max": 89000},
  "reasoning": "10 listings procesados, 2 outliers excluidos.",
  "market_context": "Mercado activo en la zona."
}

Si todos los datos son inválidos: {"error": "datos_insuficientes", "reason": "descripción"}
PROMPT,

            'rent.analysis' => <<<'PROMPT'
Eres un analista de mercado inmobiliario especializado en rentas en Ciudad de México.
Tu tarea: calcular precio de renta mensual por m² ($/m²/mes) a partir de listings crudos.

Listings de {type_label} en RENTA en Colonia {colonia}, Benito Juárez, CDMX:

LISTINGS:
{listings}

ANÁLISIS:

1. VALIDACIÓN — Descarta con precio_renta fuera de ${range_min}–${range_max} MXN/mes, m2 < 15 o m2 > 1000

2. CÁLCULO — precio_m2_mes = precio_renta / m2
   Rango razonable: ${price_m2_min}–${price_m2_max} MXN/m²/mes

3. CLASIFICACIÓN
   {age_note}
   Omite categorías con < 2 listings válidos

4. ESTADÍSTICAS (por categoría con ≥ 2 listings)
   - low = P25, avg = mediana, high = P75 del precio_m2_mes
   - Redondear a entero

5. JERARQUÍA — {hierarchy_note}
   Si se viola: omite esa categoría

6. CONFIANZA: "high" ≥5 listings, "medium" 2–4, "low" <2

Responde ÚNICAMENTE con este JSON, sin markdown:
{
  "prices": {
    "new": {"low": 220, "avg": 270, "high": 320},
    "mid": {"low": 180, "avg": 210, "high": 250},
    "old": {"low": 150, "avg": 170, "high": 200}
  },
  "confidence": "high",
  "listings_analyzed": 11,
  "outliers_excluded": 2,
  "reasoning": "11 listings procesados. Mediana seminuevo $210/m²/mes.",
  "market_context": "Mercado de renta activo en la zona."
}

Si datos insuficientes: {"error": "datos_insuficientes", "reason": "descripción"}
PROMPT,
        ];
    }
}
