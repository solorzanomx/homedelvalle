<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomEmailTemplateRequest;
use App\Http\Requests\UpdateCustomEmailTemplateRequest;
use App\Models\CustomEmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomEmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomEmailTemplate::with('creator', 'assignments');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('template_type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.email.custom-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email.custom-templates.create');
    }

    public function store(StoreCustomEmailTemplateRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();
        $validated['available_placeholders'] = $this->extractPlaceholders(
            $validated['subject'],
            $validated['html_body']
        );

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $template = CustomEmailTemplate::create($validated);

        return redirect()
            ->route('admin.custom-templates.edit', $template)
            ->with('success', 'Template creado exitosamente');
    }

    public function edit(CustomEmailTemplate $custom_template)
    {
        $template = $custom_template;
        $assignments = $template->assignments()->with('template')->get();

        return view('admin.email.custom-templates.edit', compact('template', 'assignments'));
    }

    public function update(UpdateCustomEmailTemplateRequest $request, CustomEmailTemplate $custom_template)
    {
        $validated = $request->validated();
        $validated['available_placeholders'] = $this->extractPlaceholders(
            $validated['subject'],
            $validated['html_body']
        );

        if ($validated['status'] === 'published' && !$custom_template->published_at) {
            $validated['published_at'] = now();
        }

        if ($validated['status'] === 'archived' && !$custom_template->archived_at) {
            $validated['archived_at'] = now();
        }

        $custom_template->update($validated);

        return back()->with('success', 'Template actualizado exitosamente');
    }

    public function destroy(CustomEmailTemplate $custom_template)
    {
        $name = $custom_template->name;
        $custom_template->delete();

        return redirect()
            ->route('admin.custom-templates.index')
            ->with('success', "Template '{$name}' eliminado exitosamente");
    }

    public function preview(CustomEmailTemplate $custom_template, Request $request)
    {
        $sampleData = $this->getSampleData($request->get('dataset', 'generic'));
        $html = $custom_template->render($sampleData);

        return response()->json(['html' => $html]);
    }

    public function test(CustomEmailTemplate $custom_template, Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
            'dataset' => 'nullable|in:generic,seller,buyer,developer',
        ]);

        try {
            $sampleData = $this->getSampleData($request->get('dataset', 'generic'));
            $custom_template->send($request->get('test_email'), $sampleData);

            return back()->with('success', 'Email de prueba enviado exitosamente');
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['test_email' => 'Error al enviar email: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function clone(CustomEmailTemplate $custom_template)
    {
        $newTemplate = $custom_template->replicate();
        $newTemplate->name = "{$custom_template->name} (Copia)";
        $newTemplate->slug = Str::slug($newTemplate->name) . '-' . Str::random(5);
        $newTemplate->status = 'draft';
        $newTemplate->created_by = auth()->id();
        $newTemplate->published_at = null;
        $newTemplate->archived_at = null;
        $newTemplate->save();

        return redirect()
            ->route('admin.custom-templates.edit', $newTemplate)
            ->with('success', 'Template clonado exitosamente');
    }

    private function extractPlaceholders($subject, $html): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $subject . ' ' . $html, $matches);
        return array_unique($matches[1] ?? []);
    }

    private function getSampleData(string $dataset): array
    {
        return match ($dataset) {
            'seller' => [
                'nombre' => 'Carlos Mendoza',
                'email' => 'carlos@example.com',
                'colonia' => 'Polanco',
                'metros' => '120 m²',
                'precio' => '$800,000 MXN',
                'direccion' => 'Avenida Paseo de la Reforma 505',
            ],
            'buyer' => [
                'nombre' => 'María García',
                'email' => 'maria@example.com',
                'budget' => '$2M - $3M MXN',
                'ubicacion' => 'Roma Norte',
                'tipo_propiedad' => 'Departamento',
            ],
            'developer' => [
                'nombre' => 'Dev Team',
                'email' => 'devs@example.com',
                'proyecto' => 'Towers Luna',
                'fases' => '3 fases',
                'unidades' => '150 unidades',
            ],
            default => [
                'nombre' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'fecha' => now()->format('d/m/Y H:i'),
                'folio' => 'HDV-' . strtoupper(Str::random(4)) . '-' . rand(1000, 9999),
            ],
        };
    }
}
