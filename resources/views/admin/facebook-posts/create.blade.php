@extends('layouts.app-sidebar')
@section('title', 'Nuevo Post Facebook')

@section('content')
<style>
.card { background:#fff; border-radius:12px; border:1px solid var(--border); max-width:560px; }
.card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--border); }
.card-title  { font-weight:700; font-size:.95rem; }
.card-body   { padding:1.25rem; }
.form-label  { display:block; font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:.35rem; }
.form-input, .form-select { width:100%; padding:.55rem .75rem; border:1px solid var(--border); border-radius:8px; font-size:.88rem; background:#fff; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-outline { background:#fff; color:var(--text); border:1px solid var(--border); }
.tpl-cards   { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
.tpl-card    { border:2px solid var(--border); border-radius:10px; padding:.75rem; cursor:pointer; transition:all .15s; }
.tpl-card:has(input:checked) { border-color:var(--primary); background:#eff6ff; }
.tpl-swatch  { height:28px; border-radius:5px; margin-bottom:.5rem; }
.tpl-name    { font-weight:700; font-size:.82rem; }
.tpl-desc    { font-size:.73rem; color:var(--text-muted); }
</style>

<div style="margin-bottom:1.5rem;">
    <h2 style="margin:0;">&#128241; Nuevo Post Facebook</h2>
</div>

<form method="POST" action="{{ route('admin.facebook.store') }}">
    @csrf
    <div class="card">
        <div class="card-header"><span class="card-title">Datos básicos</span></div>
        <div class="card-body">
            <div style="margin-bottom:1rem;">
                <label class="form-label">Nombre interno <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-input" placeholder="Ej: Post blog — 5 razones para comprar en CDMX" required autofocus>
            </div>

            <div style="margin-bottom:1rem;">
                <label class="form-label">Template inicial</label>
                <div class="tpl-cards">
                    @foreach(\App\Models\FacebookPost::TEMPLATES as $key => $label)
                    @php
                        [$tplName, $tplDesc] = explode(' — ', $label . ' — ');
                        $swatches = [
                            'fb-dark'     => 'background:#0C1A2E;',
                            'fb-light'    => 'background:#ffffff;border:1px solid #e2e8f0;',
                            'fb-foto'     => 'background:linear-gradient(135deg,#0C1A2E,#1d4ed8);',
                            'fb-gradient' => 'background:linear-gradient(135deg,#1e3a8a,#3b82f6);',
                        ];
                    @endphp
                    <label class="tpl-card">
                        <input type="radio" name="template" value="{{ $key }}" {{ $key === 'fb-dark' ? 'checked' : '' }}>
                        <div class="tpl-swatch" style="{{ $swatches[$key] ?? '' }}"></div>
                        <div class="tpl-name">{{ $tplName }}</div>
                        <div class="tpl-desc">{{ $tplDesc }}</div>
                    </label>
                    @endforeach
                </div>
                <p style="font-size:.75rem;color:var(--text-muted);margin-top:.5rem;">Puedes cambiarlo en el editor</p>
            </div>

            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <a href="{{ route('admin.facebook.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear y editar →</button>
            </div>
        </div>
    </div>
</form>
@endsection
