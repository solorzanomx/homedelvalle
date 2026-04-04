@extends('layouts.app-sidebar')
@section('title', 'Pagina Nosotros')

@section('content')
<div class="page-header">
    <div>
        <h2>Pagina Nosotros</h2>
        <p class="text-muted">Administra el contenido de la pagina publica /nosotros</p>
    </div>
    <a href="{{ url('/nosotros') }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;">
        <x-icon name="external-link" class="w-4 h-4" />
        Ver pagina
    </a>
</div>

<form method="POST" action="{{ route('admin.nosotros-page.update') }}">
    @csrf

    @php $content = $settings?->nosotros_content ?? []; @endphp

    {{-- 1. Mision y Vision --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Mision y Vision</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Mision</label>
                <textarea name="mission" class="form-textarea" rows="3" placeholder="Nuestra mision...">{{ old('mission', $content['mission'] ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Vision</label>
                <textarea name="vision" class="form-textarea" rows="3" placeholder="Nuestra vision...">{{ old('vision', $content['vision'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 2. Historia --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Historia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="story_heading" class="form-input" value="{{ old('story_heading', $content['story_heading'] ?? 'Nuestra Historia') }}" placeholder="Nuestra Historia">
            </div>
            <div class="form-group">
                <label class="form-label">Texto principal</label>
                <textarea name="about_text" class="form-textarea" rows="5" placeholder="Cuenta la historia de la empresa...">{{ old('about_text', $settings->about_text ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 3. Filosofia --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Filosofia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="philosophy_heading" class="form-input" value="{{ old('philosophy_heading', $content['philosophy_heading'] ?? 'Nuestra Filosofia') }}" placeholder="Nuestra Filosofia">
            </div>
            <div class="form-group">
                <label class="form-label">Texto de filosofia</label>
                <textarea name="philosophy_text" class="form-textarea" rows="4" placeholder="Describe la filosofia de la empresa...">{{ old('philosophy_text', $content['philosophy_text'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 4. Valores --}}
    @php
        $defaultValues = [
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
        ];
        $values = $content['values'] ?? $defaultValues;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Valores</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($values as $i => $value)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Valor {{ $i + 1 }}</div>
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="values[{{ $i }}][title]" class="form-input" value="{{ old("values.$i.title", $value['title'] ?? '') }}" placeholder="Nombre del valor">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripcion</label>
                        <input type="text" name="values[{{ $i }}][description]" class="form-input" value="{{ old("values.$i.description", $value['description'] ?? '') }}" placeholder="Descripcion breve del valor">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 5. Estadisticas --}}
    @php
        $defaultStats = [
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
        ];
        $stats = $content['stats'] ?? $defaultStats;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Estadisticas</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($stats as $i => $stat)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Estadistica {{ $i + 1 }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Valor</label>
                            <input type="text" name="stats[{{ $i }}][value]" class="form-input" value="{{ old("stats.$i.value", $stat['value'] ?? '') }}" placeholder="Ej: 500+">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input type="text" name="stats[{{ $i }}][label]" class="form-input" value="{{ old("stats.$i.label", $stat['label'] ?? '') }}" placeholder="Ej: Propiedades vendidas">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 6. Equipo --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Equipo</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="team_heading" class="form-input" value="{{ old('team_heading', $content['team_heading'] ?? 'Nuestro Equipo') }}" placeholder="Nuestro Equipo">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitulo</label>
                <input type="text" name="team_subheading" class="form-input" value="{{ old('team_subheading', $content['team_subheading'] ?? '') }}" placeholder="Conoce a los profesionales detras de Home del Valle">
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="p-save-bar">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>

{{-- 7. Miembros del equipo (fuera del form principal) --}}
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h3>Miembros visibles en el sitio</h3>
        <span style="font-size:0.78rem;color:var(--text-muted);">Activa o desactiva quienes aparecen en la seccion "Nuestro equipo"</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Puesto</th>
                        <th>Biografia</th>
                        <th style="text-align:center;">Visible</th>
                        <th style="text-align:center;">Orden</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr style="{{ $user->show_on_website ? '' : 'opacity:0.6;' }}">
                        <td>
                            <div class="user-cell">
                                <div class="avatar">
                                    @if($user->avatar_path)
                                        <img src="{{ Storage::url($user->avatar_path) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div style="font-weight:600;">{{ $user->full_name }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->title)
                                <span class="badge badge-blue">{{ $user->title }}</span>
                            @else
                                <span style="color:var(--text-muted);font-size:0.82rem;">Sin puesto</span>
                            @endif
                        </td>
                        <td>
                            @if($user->bio)
                                <span style="font-size:0.82rem;color:var(--text-muted);" title="{{ $user->bio }}">{{ Str::limit($user->bio, 60) }}</span>
                            @else
                                <span style="color:var(--danger);font-size:0.82rem;">Sin biografia</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <form method="POST" action="{{ route('admin.nosotros-page.toggle-team', $user) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $user->show_on_website ? 'btn-primary' : 'btn-outline' }}" title="{{ $user->show_on_website ? 'Ocultar del sitio' : 'Mostrar en el sitio' }}">
                                    {{ $user->show_on_website ? 'Visible' : 'Oculto' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align:center;">
                            <input type="number" value="{{ $user->website_order }}" min="0" max="99" style="width:50px;text-align:center;padding:0.25rem;border:1px solid var(--border);border-radius:4px;font-size:0.82rem;"
                                   onchange="updateOrder({{ $user->id }}, this.value)">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:1rem;font-size:0.78rem;color:var(--text-muted);border-top:1px solid var(--border);">
            <strong>Tip:</strong> El puesto y la biografia se editan desde el perfil de cada usuario. Los miembros se ordenan por el numero de orden (menor primero).
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateOrder(userId, order) {
    fetch('{{ route("admin.nosotros-page.team-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId, order: parseInt(order) })
    });
}
</script>
@endsection
