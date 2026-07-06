<?php

namespace App\Observers;

use App\Models\Document;

class DocumentObserver
{
    /**
     * Mismo patrón que OperationObserver. Cierra el hueco real encontrado en
     * la auditoría 2026-07-04: RentalDocumentController valida category
     * contra Document::CATEGORIES, pero PortalDocumentController solo exige
     * 'required|string' (acepta cualquier valor) — con esto ambos caminos, y
     * cualquier otro futuro, quedan protegidos por igual.
     */
    public function creating(Document $document): void
    {
        if ($document->category && !array_key_exists($document->category, Document::CATEGORIES)) {
            throw new \InvalidArgumentException("Document::category invalido: '{$document->category}'.");
        }

        if ($document->status && !array_key_exists($document->status, Document::STATUSES)) {
            throw new \InvalidArgumentException("Document::status invalido: '{$document->status}'.");
        }
    }
}
