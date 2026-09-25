<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/** Historial de un documento: subida, avisos de calidad, aprobación/rechazo, avisos al cliente. */
class DocumentEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['document_id', 'user_id', 'type', 'note', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    const LABELS = [
        'uploaded' => 'Subido',
        'quality_warn' => 'Calidad dudosa',
        'verified' => 'Aprobado',
        'rejected' => 'Rechazado',
        'notified' => 'Cliente avisado',
        'reminded' => 'Recordatorio enviado',
        'viewed' => 'Consultado',
    ];

    public function document() { return $this->belongsTo(Document::class); }
    public function user() { return $this->belongsTo(User::class); }

    /**
     * Bitácora de ACCESO a documentos sensibles (quién los abrió/descargó y cuándo). Sin repetir el mismo
     * usuario+documento dentro de 10 minutos para no llenar el historial al navegar con el visor.
     */
    public static function logAccess(Document $document, string $how): void
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }
        $key = "docaccess:{$userId}:{$document->id}";
        if (\Illuminate\Support\Facades\Cache::has($key)) {
            return;
        }
        \Illuminate\Support\Facades\Cache::put($key, 1, now()->addMinutes(10));
        static::log($document, 'viewed', $how, $userId);
    }

    /** Registra un evento; nunca debe romper el flujo principal. */
    public static function log(Document|int $document, string $type, ?string $note = null, ?int $userId = null): void
    {
        try {
            static::create([
                'document_id' => $document instanceof Document ? $document->id : $document,
                'user_id' => $userId ?? Auth::id(),
                'type' => $type,
                'note' => $note,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('DocumentEvent::log omitido', ['error' => $e->getMessage()]);
        }
    }
}
