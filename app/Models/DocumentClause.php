<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Texto editable de una cláusula/sección de un documento de marca (hoy solo
 * usado por Carta Oferta de Compra) — ver App\Http\Controllers\Admin\DocumentClauseController.
 * Si no hay fila para un document_key/clause_key, el documento usa su
 * texto por defecto (ver PurchaseOfferGeneratorService::DEFAULT_CLAUSES).
 */
class DocumentClause extends Model
{
    protected $fillable = ['document_key', 'clause_key', 'value', 'updated_by'];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Devuelve el texto guardado para esta cláusula, o $default si no se ha
     * personalizado. $tokens reemplaza placeholders tipo {{vigencia_dias}}
     * de forma literal (str_replace, NO se evalúa como Blade/PHP).
     */
    public static function text(string $documentKey, string $clauseKey, string $default, array $tokens = []): string
    {
        $value = static::where('document_key', $documentKey)
            ->where('clause_key', $clauseKey)
            ->value('value') ?? $default;

        foreach ($tokens as $token => $replacement) {
            $value = str_replace('{{' . $token . '}}', $replacement, $value);
        }

        return $value;
    }
}
