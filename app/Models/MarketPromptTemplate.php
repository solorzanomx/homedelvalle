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
            'sale.search'       => 'Búsqueda de venta — colonia (legacy)',
            'rent.search'       => 'Búsqueda de renta — colonia (legacy)',
            'sale.search.zone'  => 'Búsqueda de venta — zona (Perplexity)',
            'rent.search.zone'  => 'Búsqueda de renta — zona (Perplexity)',
            'sale.analysis'     => 'Análisis de venta (Claude)',
            'rent.analysis'     => 'Análisis de renta (Claude)',
        ];
    }

    public static function descriptions(): array
    {
        return [
            'sale.search'       => 'Búsqueda por colonia individual. Variables: {colonia}, {type_label}, {fields}',
            'rent.search'       => 'Búsqueda por colonia individual. Variables: {colonia}, {type_label}, {fields}',
            'sale.search.zone'  => 'Búsqueda por zona (4–6 colonias juntas) → más listings → mayor confianza. Variables: {zone_name}, {colony_list}, {type_label}, {fields}, {op_label}',
            'rent.search.zone'  => 'Búsqueda de renta por zona. Variables: {zone_name}, {colony_list}, {type_label}, {fields}, {op_label}',
            'sale.analysis'     => 'Claude analiza los listings y calcula precio/m² por categoría de edad. Variables: {colonia}, {type_label}, {listings}',
            'rent.analysis'     => 'Claude calcula precio de renta mensual por m². Variables: {colonia}, {type_label}, {listings}, {range_min}, {range_max}, {price_m2_min}, {price_m2_max}, {age_note}, {hierarchy_note}',
        ];
    }

    public static function variables(): array
    {
        return [
            'sale.search'      => ['{colonia}', '{type_label}', '{fields}'],
            'rent.search'      => ['{colonia}', '{type_label}', '{fields}'],
            'sale.search.zone' => ['{zone_name}', '{colony_list}', '{type_label}', '{fields}', '{op_label}'],
            'rent.search.zone' => ['{zone_name}', '{colony_list}', '{type_label}', '{fields}', '{op_label}'],
            'sale.analysis'    => ['{colonia}', '{type_label}', '{listings}'],
            'rent.analysis'    => ['{colonia}', '{type_label}', '{listings}', '{range_min}', '{range_max}', '{price_m2_min}', '{price_m2_max}', '{age_note}', '{hierarchy_note}'],
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

            'sale.search.zone' => <<<'PROMPT'
Search for current apartment FOR SALE listings in Benito Juárez, Mexico City.
Target neighborhoods: {colony_list}.

Search these portals: Inmuebles24, Lamudi, Propiedades.com, MercadoLibre Inmuebles, Metros Cúbicos, Vivanuncios, easybroker, Encuentra24.

CRITICAL: Only include listings where you can find BOTH the sale price AND the construction area (m²). Skip listings missing either field.

For m²: Look for labels like "construcción", "sup. construida", "m² construidos", "área construida". Do NOT use "terreno" or "lote" area.

For each valid listing extract:
{fields}
- "colonia": neighborhood (e.g. "Narvarte Oriente")
- "fuente": portal name

Return 10 to 20 listings as a JSON array only, no markdown, no extra text:
[
  {"precio": 4200000, "m2": 85, "antiguedad": 8, "recamaras": 2, "piso": 3, "condicion": "seminuevo", "colonia": "Narvarte Oriente", "fuente": "Inmuebles24"},
  {"precio": 7800000, "m2": 148, "antiguedad": 3, "recamaras": 3, "piso": 5, "condicion": "nuevo", "colonia": "Narvarte Poniente", "fuente": "Lamudi"},
  {"precio": 3500000, "m2": 72, "antiguedad": 30, "recamaras": 2, "piso": 1, "condicion": "antiguo", "colonia": "Vértiz Narvarte", "fuente": "Metros Cúbicos"}
]
PROMPT,

            'rent.search.zone' => <<<'PROMPT'
Necesito anuncios ACTUALES de {type_label} en {op_label} en la Zona {zone_name}, alcaldía Benito Juárez, Ciudad de México.

Esta zona comprende las colonias: {colony_list}.

REGLAS IMPORTANTES:
1. Solo anuncios de renta directa (no subarrendamiento, no temporada)
2. precio_renta = renta mensual publicada en el portal (sin incluir mantenimiento ni gastos)
3. m2 = metros cuadrados de CONSTRUCCIÓN (NO de terreno)
4. Prioriza anuncios publicados en los últimos 6 meses
5. Distribuye los anuncios entre las colonias de la zona
6. Incluye campo "condicion" para ayudar a clasificar la antigüedad

Busca en: Inmuebles24, Lamudi, Vivanuncios, Propiedades.com, MercadoLibre Inmuebles, Metros Cúbicos, easybroker, Encuentra24, o cualquier portal disponible.

Por cada anuncio, extrae:
{fields}
- "colonia": nombre de la colonia dentro de la zona
- "fuente": nombre del portal

Devuelve entre 10 y 20 anuncios reales de toda la zona.

Responde ÚNICAMENTE con un JSON array, sin texto adicional ni markdown:
[
  {"precio_renta": 22000, "m2": 85, "antiguedad": 8, "recamaras": 2, "condicion": "seminuevo", "colonia": "Narvarte Oriente", "fuente": "Inmuebles24"}
]

Si encuentras menos de 3 anuncios en toda la zona, responde: {"error": "sin_datos"}
PROMPT,

            'sale.analysis' => <<<'PROMPT'
Eres un analista de mercado inmobiliario con experiencia en estadística de bienes raíces en México.
Tu tarea: procesar listings crudos y producir estadísticas confiables de precio por m² segmentadas por antigüedad.

Listings de {type_label} en VENTA en {colonia}, Benito Juárez, CDMX:

LISTINGS:
{listings}

INSTRUCCIONES:

1. VALIDACIÓN — Descarta listings con:
   - precio < 500,000 o > 50,000,000 MXN
   - sin precio

2. PRECIO/M²:
   a) Si el listing tiene m2 válido (20–1000): precio_m2 = precio / m2
   b) Si m2 es null pero tiene "recamaras": estima m2 típico para BJ
      1 rec → 55m2 | 2 rec → 80m2 | 3 rec → 110m2 | 4 rec → 140m2
      Marca estos como estimados.
   c) Si no tiene m2 ni recamaras: descartar.
   Rango razonable en BJ: $30,000–$180,000 MXN/m²

3. OUTLIERS — Excluye donde precio_m2 < (Q1 - 1.5×IQR) o > (Q3 + 1.5×IQR)

4. CLASIFICACIÓN POR ANTIGÜEDAD (jerarquía de señales):
   a) "antiguedad" explícita → "new": 0–10 años | "mid": 11–25 años | "old": >25 años
   b) "condicion": "nuevo"/"estrenar" → "new" | "seminuevo" → "mid" | "remodelar"/"renovar" → "old"
   c) Proxy precio_m2: >$85,000 → "new" | $52,000–$85,000 → "mid" | <$52,000 → "old"

5. ESTADÍSTICAS (por categoría):
   low = P25, avg = mediana, high = P75 del precio_m2 (entero)

6. POLÍTICA DE REPORTE:
   - ≥ 5 listings → confidence "high"
   - 2–4 listings → confidence "medium"
   - 1 listing → confidence "low" (REPORTAR igual, no omitir)
   - 0 listings → omitir esa categoría
   Es mejor dato con confianza baja que no tener dato.

7. JERARQUÍA — En BJ: nuevo > seminuevo > antiguo. Si se viola por muestra pequeña, reportar con confidence "low".

Responde ÚNICAMENTE con este JSON exacto, sin markdown:
{
  "prices": {
    "new": {"low": 85000, "avg": 95000, "high": 110000},
    "mid": {"low": 65000, "avg": 74000, "high": 83000},
    "old": {"low": 48000, "avg": 55000, "high": 63000}
  },
  "confidence": "high",
  "listings_analyzed": 18,
  "outliers_excluded": 2,
  "price_m2_range": {"min": 52000, "max": 115000},
  "reasoning": "18 listings. 6 nuevos (proxy precio >85k), 9 seminuevos, 3 antiguos.",
  "market_context": "Mercado activo, buena demanda en la zona."
}

Si todos los datos son inválidos: {"error": "datos_insuficientes", "reason": "descripción"}
PROMPT,

            'rent.analysis' => <<<'PROMPT'
Eres un analista de mercado inmobiliario especializado en rentas en Ciudad de México.
Tu tarea: calcular precio de renta mensual por m² ($/m²/mes) segmentado por antigüedad.

Listings de {type_label} en RENTA en {colonia}, Benito Juárez, CDMX:

LISTINGS:
{listings}

ANÁLISIS:

1. VALIDACIÓN — Descarta con precio_renta fuera de ${range_min}–${range_max} MXN/mes, m2 < 15 o m2 > 1000

2. CÁLCULO — precio_m2_mes = precio_renta / m2
   Rango razonable para BJ: ${price_m2_min}–${price_m2_max} MXN/m²/mes
   Descarta outliers fuera de ese rango

3. CLASIFICACIÓN POR ANTIGÜEDAD (jerarquía de señales):

   a) Campo "antiguedad" explícito → úsalo directamente
      {age_note}

   b) Campo "condicion":
      "nuevo" → "new" | "seminuevo" → "mid" | "a remodelar" → "old"

   c) Proxy precio_m2_mes cuando no hay señal:
      precio_m2_mes > $220/m²/mes → "new"
      precio_m2_mes entre $140 y $220 → "mid"
      precio_m2_mes < $140 → "old"

4. ESTADÍSTICAS (por categoría):
   - low = P25, avg = mediana, high = P75 del precio_m2_mes (redondear a entero)

5. POLÍTICA DE REPORTE:
   - ≥ 5 listings → confidence "high"
   - 2–4 listings → confidence "medium"
   - 1 listing → confidence "low" (REPORTAR, no omitir)
   - 0 listings → omitir esa categoría

6. JERARQUÍA — {hierarchy_note}
   Si se viola, reportar igual con confidence "low"

Responde ÚNICAMENTE con este JSON, sin markdown:
{
  "prices": {
    "new": {"low": 250, "avg": 310, "high": 380},
    "mid": {"low": 190, "avg": 230, "high": 275},
    "old": {"low": 150, "avg": 175, "high": 210}
  },
  "confidence": "high",
  "listings_analyzed": 14,
  "outliers_excluded": 2,
  "reasoning": "14 listings. 4 nuevos, 7 seminuevos (proxy precio), 3 antiguos.",
  "market_context": "Demanda activa de renta en la zona."
}

Si datos insuficientes: {"error": "datos_insuficientes", "reason": "descripción"}
PROMPT,
        ];
    }
}
