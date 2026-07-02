<?php

namespace App\Models\Concerns;

use App\Support\Attribution;

/**
 * Rellena landing_post_id, landing_label, utm_source/medium/campaign y
 * referrer desde la atribución de primer contacto guardada en sesión
 * (App\Support\Attribution), solo en los campos que el modelo ya tiene
 * fillable y que vienen vacíos del request actual — no pisa un UTM real
 * que el propio submit haya capturado.
 *
 * Se aplica a los 3 modelos de conversión (ContactSubmission, FormSubmission,
 * NewsletterSubscriber) sin tocar los ~9 puntos donde se crean.
 */
trait HasAttribution
{
    public static function bootHasAttribution(): void
    {
        static::creating(function ($model) {
            $fillable = $model->getFillable();

            foreach (Attribution::fields() as $key => $value) {
                if ($value !== null && in_array($key, $fillable, true) && empty($model->{$key})) {
                    $model->{$key} = $value;
                }
            }
        });
    }
}
