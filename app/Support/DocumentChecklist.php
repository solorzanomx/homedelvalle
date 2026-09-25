<?php

namespace App\Support;

use App\Models\Document;
use Illuminate\Support\Collection;

/**
 * Arma la vista de documentos de un trato a partir de "secciones" (qué
 * categorías aplican): por sección, las categorías con archivos, las que
 * faltan, y "otros" (subidos fuera de lista — nada se oculta jamás).
 * Lo usan RentalDocumentChecklist (rentas) y OperationDocumentChecklist (ventas).
 */
class DocumentChecklist
{
    /** Sección con las etiquetas oficiales de Document::CATEGORIES. */
    public static function section(string $key, string $title, string $icon, array $categoryKeys, string $client = 'any'): array
    {
        $labels = Document::CATEGORIES;
        $cats = [];
        foreach ($categoryKeys as $k) {
            $cats[$k] = $labels[$k] ?? $k;
        }

        return compact('key', 'title', 'icon', 'client') + ['categories' => $cats];
    }

    /**
     * @param  Collection<int, Document>  $docs
     * @param  callable(Document, array): bool  $belongs  ¿el documento pertenece a esta sección (por persona)?
     */
    public static function assemble(Collection $docs, array $sections, callable $belongs): array
    {
        $docs = $docs->sortByDesc('created_at')->values();
        $shown = [];
        $out = [];

        foreach ($sections as $section) {
            $present = [];
            $missing = [];
            foreach ($section['categories'] as $key => $label) {
                $catDocs = $docs->filter(fn($d) => $d->category === $key && $belongs($d, $section))
                    ->reject(fn($d) => isset($shown[$d->id]))->values();
                foreach ($catDocs as $d) {
                    $shown[$d->id] = true;
                }
                $catDocs->isEmpty() ? $missing[$key] = $label : $present[] = ['key' => $key, 'label' => $label, 'docs' => $catDocs];
            }
            $out[] = $section + ['present' => $present, 'missing' => $missing];
        }

        return ['sections' => $out, 'others' => $docs->reject(fn($d) => isset($shown[$d->id]))->values()];
    }
}
