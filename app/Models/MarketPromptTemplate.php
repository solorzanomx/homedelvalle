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
Busca anuncios ACTUALES de {type_label} en VENTA en la Zona {zone_name}, alcaldía Benito Juárez, Ciudad de México.

Esta zona comprende las colonias: {colony_list}.

Busca activamente en: Inmuebles24, Lamudi, Vivanuncios, Propiedades.com, MercadoLibre Inmuebles, Metros Cúbicos, easybroker, Encuentra24, y cualquier portal inmobiliario disponible.

Si no encuentras suficientes anuncios en estas colonias específicamente, amplía la búsqueda a colonias adyacentes dentro de Benito Juárez.

Por cada anuncio encontrado extrae:
{fields}
- "colonia": colonia o zona aproximada
- "fuente": nombre del portal

IMPORTANTE: Solo "precio" y "m2" son obligatorios. Los campos "antiguedad", "recamaras", "piso" y "condicion" pueden ser null si no están disponibles.
m2 = metros cuadrados de CONSTRUCCIÓN (no terreno). Precio = precio de lista en MXN (número entero).

Devuelve entre 8 y 20 anuncios. Omite solo los que no tengan precio ni m².

Responde ÚNICAMENTE con un JSON array, sin texto adicional ni markdown:
[
  {"precio": 3800000, "m2": 78, "antiguedad": 12, "recamaras": 2, "piso": 3, "condicion": "seminuevo", "colonia": "Narvarte Oriente", "fuente": "Inmuebles24"},
  {"precio": 7500000, "m2": 140, "antiguedad": null, "recamaras": 3, "piso": null, "condicion": null, "colonia": "Narvarte Poniente", "fuente": "Lamudi"}
]

Si no encuentras absolutamente ningún anuncio con precio y m² válidos, responde: {"error": "sin_datos"}
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
   - m2 < 20 o m2 > 1000
   - sin precio o sin m2

2. PRECIO/M² — precio_m2 = precio / m2
   Rango razonable en Benito Juárez: $30,000–$180,000 MXN/m²

3. OUTLIERS — Excluye donde precio_m2 < (Q1 - 1.5×IQR) o > (Q3 + 1.5×IQR)

4. CLASIFICACIÓN POR ANTIGÜEDAD (usa la siguiente jerarquía de señales):

   a) Si el listing tiene "antiguedad" explícita → úsala directamente
      "new": 0–10 años | "mid": 11–25 años | "old": más de 25 años

   b) Si tiene campo "condicion":
      "nuevo" o "estrenar" → "new"
      "seminuevo" → "mid"
      "a remodelar" o "por renovar" → "old"

   c) Si no hay señal de edad, usa precio_m2 como PROXY:
      precio_m2 > $85,000 → "new"
      precio_m2 entre $52,000 y $85,000 → "mid"
      precio_m2 < $52,000 → "old"

5. ESTADÍSTICAS (por categoría):
   - low = P25, avg = mediana, high = P75 del precio_m2 (redondear a entero)

6. POLÍTICA DE REPORTE (importante):
   - ≥ 5 listings en categoría → confidence "high"
   - 2–4 listings en categoría → confidence "medium"
   - 1 listing en categoría → confidence "low" (REPORTAR igual, no omitir)
   - 0 listings en categoría → omitir esa categoría
   Es mejor un rango con confianza baja que no tener dato.

7. JERARQUÍA — En BJ: nuevo > seminuevo > antiguo en precio/m²
   Si se viola por muestra pequeña, reportar de todos modos con confidence "low"

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
