@extends('layouts.app-sidebar')
@section('title', 'Preview - ' . $templateName)

@section('content')
<div class="page-header">
    <div>
        <a href="{{ route('admin.transactional-emails.index') }}" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">← Volver</a>
        <h2 style="margin-top: 0.5rem;">{{ $templateName }}</h2>
        <p class="text-muted">{{ $description }}</p>
    </div>
</div>

{{-- Success/Error Messages --}}
@if($message = session('success'))
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #047857; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
        {{ $message }}
    </div>
@endif

@if($message = session('error'))
    <div style="background: #fee; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
        {{ $message }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    {{-- Preview Panel --}}
    <div>
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Preview del Email</h3>
            </div>
            <div class="card-body" style="padding: 0; background: #f5f5f5; overflow-x: auto;">
                <div style="display: flex; justify-content: center; padding: 2rem 0; background: #f5f5f5;">
                    <iframe
                        src="{{ route('admin.transactional-emails.render', $templateId) }}"
                        style="width: 620px; max-width: 95vw; height: 700px; border: none; border-radius: 8px; display: block; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                        sandbox="allow-same-origin"
                    ></iframe>
                </div>
            </div>
        </div>

        {{-- Email HTML Code --}}
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 style="margin: 0;">Código HTML</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <pre style="margin: 0; overflow-x: auto; padding: 1rem; background: #f5f5f5; font-size: 0.85rem; max-height: 300px;"><code>{{ htmlspecialchars($mailableHtml) }}</code></pre>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        <div style="display: grid; grid-template-columns: 350px; gap: 1rem;">
            {{-- Send Test Email --}}
            <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">📧 Enviar Test</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.transactional-emails.send-test', $templateId) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">Email de destino</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ auth()->user()->email ?? '' }}"
                            placeholder="tu@email.com"
                            style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-size: 0.9rem; box-sizing: border-box;"
                            required
                        />
                    </div>
                    <button
                        type="submit"
                        style="width: 100%; padding: 0.75rem; background: #667eea; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;"
                        onmouseover="this.style.background='#5568d3'"
                        onmouseout="this.style.background='#667eea'"
                    >
                        Enviar Test Email
                    </button>
                </form>
            </div>
        </div>

        {{-- Template Info --}}
        <div class="card" style="margin-top: 1rem;">
            <div class="card-header">
                <h3 style="margin: 0;">ℹ️ Info del Template</h3>
            </div>
            <div class="card-body" style="font-size: 0.9rem;">
                <p style="margin: 0 0 0.5rem;"><strong>Template ID:</strong></p>
                <code style="background: #f5f5f5; padding: 0.5rem; border-radius: 3px; display: block;">{{ $templateId }}</code>

                <p style="margin: 0.75rem 0 0.5rem;"><strong>Archivos:</strong></p>
                <div style="font-size: 0.85rem;">
                    <p style="margin: 0.25rem 0;">📄 Template: <code>resources/views/emails/v4/{{ $templateId }}.blade.php</code></p>
                    <p style="margin: 0.25rem 0;">📧 Mailable: <code>app/Mail/V4/Mailables/</code></p>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
    }

    .card-header {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9f9f9;
    }

    .card-body {
        padding: 1rem;
    }

    pre {
        font-family: 'Monaco', 'Courier New', monospace;
        line-height: 1.5;
    }

    code {
        font-family: 'Monaco', 'Courier New', monospace;
    }

    a {
        color: #667eea;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    .text-muted {
        color: #999;
    }

    .page-header {
        margin-bottom: 2rem;
    }
</style>
@endsection
