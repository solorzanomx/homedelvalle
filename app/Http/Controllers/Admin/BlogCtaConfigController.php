<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCtaConfig;
use App\Support\BlogCluster;
use Illuminate\Http\Request;

class BlogCtaConfigController extends Controller
{
    public function index()
    {
        // Orden fijo (el de BlogCluster::LABELS) en PHP — evita FIELD(), que es MySQL-only y
        // rompería en SQLite local (mismo tipo de gotcha ya documentado en el proyecto).
        $order = array_keys(BlogCluster::LABELS);
        $configs = BlogCtaConfig::all()->sortBy(fn($c) => array_search($c->cluster, $order))->values();

        return view('admin.blog-ctas.index', compact('configs'));
    }

    public function update(Request $request, BlogCtaConfig $blogCtaConfig)
    {
        $validated = $request->validate([
            'form_type' => 'required|in:vendedor,vendedor_predio,comprador,arrendatario,propietario_renta,b2b,contacto',
            'headline' => 'required|string|max:200',
            'body' => 'required|string|max:600',
            'button_label' => 'required|string|max:80',
            'whatsapp_message' => 'required|string|max:400',
            'sell_headline' => 'nullable|string|max:200',
            'sell_body' => 'nullable|string|max:600',
            'sell_button_label' => 'nullable|string|max:80',
            'sell_whatsapp_message' => 'nullable|string|max:400',
        ]);

        $blogCtaConfig->update($validated);

        return back()->with('success', 'CTA de "' . (BlogCluster::LABELS[$blogCtaConfig->cluster] ?? $blogCtaConfig->cluster) . '" actualizado.');
    }
}
