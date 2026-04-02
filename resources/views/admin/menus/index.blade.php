@extends('layouts.app-sidebar')
@section('title', 'Menus')

@section('content')
<div class="page-header">
    <div>
        <h2>Menus</h2>
        <p class="text-muted">Administra los menus de navegacion del sitio</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    @foreach($menus as $menu)
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 2rem;">
            <div style="font-size: 2rem; margin-bottom: 0.75rem;">
                @if($menu->location === 'header') &#9776; @else &#9881; @endif
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 600;">{{ $menu->name }}</h3>
            <p class="text-muted" style="font-size: 0.82rem; margin-top: 0.25rem;">
                {{ $menu->location === 'header' ? 'Navegacion principal' : 'Links del footer' }}
                &middot; {{ $menu->all_items_count }} items
            </p>
            <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-primary" style="margin-top: 1rem;">Editar menu</a>
        </div>
    </div>
    @endforeach
</div>
@endsection
