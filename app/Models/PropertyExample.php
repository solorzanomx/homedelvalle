<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODELO PROPERTY - ESTRUCTURA RECOMENDADA
 *
 * Asegúrate de que tu modelo tenga estos campos en la base de datos.
 * Ver migraciones en create_properties_table.php
 */
class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identificación
        'title',
        'slug',
        'tipo_propiedad',
        'operacion',

        // Ubicación
        'colonia',
        'alcaldia',
        'ciudad',
        'direccion',

        // Precio
        'precio',
        'moneda',

        // Dimensiones
        'terreno_m2',
        'construccion_m2',

        // Espacios
        'recamaras',
        'baños',
        'medios_baños',
        'estacionamientos',

        // Características
        'antigüedad',
        'nivel',
        'uso_suelo',
        'estado_conservacion',
        'estatus_legal',

        // Descripción
        'descripcion',
        'amenidades',
        'observaciones',

        // Multimedia
        'qr_path',

        // Relaciones
        'user_id',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'terreno_m2' => 'decimal:2',
        'construccion_m2' => 'decimal:2',
        'recamaras' => 'integer',
        'baños' => 'integer',
        'medios_baños' => 'integer',
        'estacionamientos' => 'integer',
        'amenidades' => 'array', // Si usas JSON
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Propietario o broker de la propiedad
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Imágenes de la propiedad
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order', 'asc');
    }

    // ========================================
    // ATRIBUTOS Y MUTADORES
    // ========================================

    /**
     * Precio formateado para mostrar
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->precio) {
            return 'Contactar';
        }
        return $this->moneda . ' ' . number_format($this->precio, 0);
    }

    /**
     * URL de slug para rutas
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ========================================
    // MÉTODOS ÚTILES
    // ========================================

    /**
     * Genera un slug automático del título
     */
    public static function generateSlug(string $title): string
    {
        return \Str::slug($title . '-' . now()->timestamp);
    }

    /**
     * Obtiene la imagen principal
     */
    public function getMainImageAttribute()
    {
        return $this->images()->first();
    }

    /**
     * Obtiene todas las amenidades como array
     */
    public function getAmenitiesArrayAttribute(): array
    {
        if (is_array($this->amenidades)) {
            return $this->amenidades;
        }

        if (!$this->amenidades) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(',', $this->amenidades))
        );
    }

    /**
     * Verifica si tiene imágenes
     */
    public function hasImages(): bool
    {
        return $this->images()->count() > 0;
    }

    /**
     * Verifica si tiene QR generado
     */
    public function hasQrCode(): bool
    {
        return !empty($this->qr_path) && file_exists(storage_path('app/public/' . $this->qr_path));
    }

    /**
     * Genera un QR para la propiedad
     */
    public function generateQrCode(): void
    {
        \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
            ->format('png')
            ->generate(
                route('properties.show', $this),
                storage_path("app/public/properties/{$this->id}/qr/qr-code.png")
            );

        $this->update(['qr_path' => "properties/{$this->id}/qr/qr-code.png"]);
    }
}
