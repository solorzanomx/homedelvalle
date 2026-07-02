<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DocumentRegistryController extends Controller
{
    public function index()
    {
        $documents = collect(config('document_registry'))->map(function ($doc, $key) {
            $doc['key'] = $key;
            $doc['ultima_actualizacion'] = collect($doc['changelog'])->sortByDesc('fecha')->first();
            return $doc;
        });

        return view('admin.documentos.index', compact('documents'));
    }
}
