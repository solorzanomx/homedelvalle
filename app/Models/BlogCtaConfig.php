<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCtaConfig extends Model
{
    protected $fillable = [
        'cluster', 'form_type', 'headline', 'body', 'button_label', 'whatsapp_message',
        'sell_headline', 'sell_body', 'sell_button_label', 'sell_whatsapp_message',
    ];

    public static function forCluster(?string $cluster): ?self
    {
        if (! $cluster) {
            return null;
        }

        return static::where('cluster', $cluster)->first();
    }

    /** Copy a usar: la variante "ya decidió vender" si aplica y está cargada, si no la general. */
    public function copyFor(bool $decided): array
    {
        if ($decided && $this->sell_headline) {
            return [
                'headline' => $this->sell_headline,
                'body' => $this->sell_body,
                'button_label' => $this->sell_button_label ?: $this->button_label,
                'whatsapp_message' => $this->sell_whatsapp_message ?: $this->whatsapp_message,
            ];
        }

        return [
            'headline' => $this->headline,
            'body' => $this->body,
            'button_label' => $this->button_label,
            'whatsapp_message' => $this->whatsapp_message,
        ];
    }
}
