<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Genera la imagen OG 1200×630 para las páginas /precios/*.
 * Usa GD puro — sin dependencias externas más allá de php-gd.
 * Idempotente: sobreescribe si ya existe.
 *
 * Uso:
 *   php artisan og:generate-precios
 */
class GeneratePreciosOgImage extends Command
{
    protected $signature   = 'og:generate-precios';
    protected $description = 'Genera storage/app/public/og/precios-og.jpg para og:image en /precios/*';

    private const W = 1200;
    private const H = 630;

    // Paleta (hex → [r,g,b])
    private const NAVY     = [15,  23,  42];   // #0f172a — fondo principal
    private const NAVY2    = [22,  48,  90];   // #16305a — gradiente
    private const BRAND    = [59, 130, 196];   // #3B82C4 — acento
    private const BRAND_DK = [30,  80, 140];   // acento oscuro para elementos secundarios
    private const WHITE    = [255, 255, 255];
    private const GRAY     = [148, 163, 184];  // slate-400
    private const GRAY2    = [100, 116, 139];  // slate-500

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('La extensión GD de PHP no está disponible.');
            return self::FAILURE;
        }

        $fontBold    = resource_path('fonts/DejaVuSans-Bold.ttf');
        $fontRegular = resource_path('fonts/DejaVuSans.ttf');

        if (! file_exists($fontBold) || ! file_exists($fontRegular)) {
            $this->error('Fuentes no encontradas en resources/fonts/. Ejecuta primero el deploy script.');
            return self::FAILURE;
        }

        $img = imagecreatetruecolor(self::W, self::H);

        // ── Colores ──────────────────────────────────────────────────────────
        $cNavy    = $this->rgb($img, self::NAVY);
        $cNavy2   = $this->rgb($img, self::NAVY2);
        $cBrand   = $this->rgb($img, self::BRAND);
        $cBrandDk = $this->rgb($img, self::BRAND_DK);
        $cWhite   = $this->rgb($img, self::WHITE);
        $cGray    = $this->rgb($img, self::GRAY);
        $cGray2   = $this->rgb($img, self::GRAY2);

        // ── Fondo — gradiente horizontal navy oscuro → navy azulado ──────────
        for ($x = 0; $x < self::W; $x++) {
            $t   = $x / self::W;
            $r   = (int)(self::NAVY[0] + $t * (self::NAVY2[0] - self::NAVY[0]));
            $g   = (int)(self::NAVY[1] + $t * (self::NAVY2[1] - self::NAVY[1]));
            $b   = (int)(self::NAVY[2] + $t * (self::NAVY2[2] - self::NAVY[2]));
            $col = imagecolorallocate($img, $r, $g, $b);
            imageline($img, $x, 0, $x, self::H, $col);
        }

        // ── Barra superior de acento (brand) ─────────────────────────────────
        imagefilledrectangle($img, 0, 0, self::W, 5, $cBrand);

        // ── Panel izquierdo semitransparente ─────────────────────────────────
        // Simulado con rectángulo más oscuro
        $cPanel = imagecolorallocatealpha($img, 0, 0, 0, 80);
        imagefilledrectangle($img, 0, 0, 730, self::H, $cPanel);

        // ── Separador vertical ────────────────────────────────────────────────
        imagefilledrectangle($img, 730, 0, 732, self::H, $cBrandDk);

        // ── Texto izquierdo ───────────────────────────────────────────────────

        // "HOME DEL VALLE" — marca arriba
        imagettftext($img, 13, 0, 72, 72, $cBrand, $fontBold, 'HOME DEL VALLE');

        // Línea decorativa bajo la marca
        imagefilledrectangle($img, 72, 84, 220, 86, $cBrand);

        // Heading principal
        imagettftext($img, 52, 0, 72, 190, $cWhite, $fontBold, 'Precio por m²');

        // Sub-heading zona
        imagettftext($img, 28, 0, 72, 250, $cGray, $fontRegular, 'en Benito Juárez, CDMX');

        // Separador horizontal
        imagefilledrectangle($img, 72, 286, 620, 288, $cBrandDk);

        // Bullet points con datos clave
        $bullets = [
            '  Datos de venta y renta por colonia',
            '  Actualizado mensualmente',
            '  Departamentos, casas y locales',
        ];
        $by = 330;
        foreach ($bullets as $line) {
            // Dot
            imagefilledellipse($img, 82, $by - 5, 8, 8, $cBrand);
            imagettftext($img, 16, 0, 100, $by, $cGray, $fontRegular, ltrim($line));
            $by += 38;
        }

        // URL en la parte inferior izquierda
        imagettftext($img, 14, 0, 72, 570, $cGray2, $fontRegular, 'homedelvalle.mx/precios');

        // ── Lado derecho — gráfica de barras abstracta ────────────────────────
        $this->drawBarChart($img, $cBrand, $cBrandDk, $cNavy2, $cGray2);

        // Etiqueta derecha arriba
        imagettftext($img, 12, 0, 780, 72, $cGray2, $fontRegular, 'Precio promedio m²');
        imagettftext($img, 11, 0, 780, 92, $cGray2, $fontRegular, 'por zona · Benito Juárez');

        // Valores debajo de la gráfica (decorativos, representativos)
        $zones = ['Del Valle', 'Narvarte', 'Nápoles', 'Portales', 'Álamos'];
        $zx = 764;
        $barW = 72;
        $zStep = $barW + 16;
        foreach ($zones as $i => $zone) {
            // Nombre zona — texto muy pequeño, centrado bajo la barra
            $bbox = imagettfbbox(9, 0, $fontRegular, $zone);
            $tw = $bbox[2] - $bbox[0];
            $cx = $zx + $i * $zStep + $barW / 2;
            imagettftext($img, 9, 0, $cx - $tw / 2, 510, $cGray2, $fontRegular, $zone);
        }

        // ── Guardar ───────────────────────────────────────────────────────────
        Storage::disk('public')->makeDirectory('og');
        $outputPath = storage_path('app/public/og/precios-og.jpg');

        imagejpeg($img, $outputPath, 90);
        imagedestroy($img);

        $this->info("[OK] Imagen generada: storage/app/public/og/precios-og.jpg");
        $this->line("     Accesible en: /storage/og/precios-og.jpg");

        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function rgb(\GdImage $img, array $c): int
    {
        return imagecolorallocate($img, $c[0], $c[1], $c[2]);
    }

    /**
     * Dibuja una gráfica de barras estilizada en el lado derecho del canvas.
     * Los valores son representativos (no datos reales), solo decoración visual.
     */
    private function drawBarChart(\GdImage $img, int $cBrand, int $cBrandDk, int $cBg, int $cLabel): void
    {
        // Barras: [altura relativa 0-1, es_destacada]
        $bars = [
            ['h' => 0.78, 'hi' => false],
            ['h' => 0.92, 'hi' => true],   // Narvarte — más alta, destacada
            ['h' => 0.61, 'hi' => false],
            ['h' => 0.45, 'hi' => false],
            ['h' => 0.55, 'hi' => false],
        ];

        $startX  = 764;
        $bottomY = 490;
        $maxH    = 280;
        $barW    = 72;
        $gap     = 16;

        // Línea base
        imagefilledrectangle($img, $startX - 8, $bottomY + 2, $startX + count($bars) * ($barW + $gap) + 8, $bottomY + 3, $cLabel);

        foreach ($bars as $i => $bar) {
            $bh = (int)($bar['h'] * $maxH);
            $x1 = $startX + $i * ($barW + $gap);
            $x2 = $x1 + $barW;
            $y1 = $bottomY - $bh;
            $y2 = $bottomY;

            $color = $bar['hi'] ? $cBrand : $cBrandDk;
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);

            // Pequeño borde superior más claro
            $topColor = $bar['hi']
                ? imagecolorallocate($img, 100, 170, 220)
                : imagecolorallocate($img, 50, 110, 170);
            imagefilledrectangle($img, $x1, $y1, $x2, $y1 + 3, $topColor);
        }
    }
}
