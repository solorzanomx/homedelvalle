@extends('layouts.app-sidebar')
@section('title', 'Calendario Social — ' . \Carbon\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY'))

@section('content')
<style>
:root { --cal-blue:#1d4ed8; --cal-pink:#ec4899; --cal-green:#10b981; --cal-purple:#7c3aed; --cal-orange:#f59e0b; }
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem; }
.page-title  { font-size:1.35rem; font-weight:800; color:#0C1A2E; margin:0; }
.month-nav   { display:flex; align-items:center; gap:.5rem; }
.month-label { font-size:1.05rem; font-weight:700; color:#0C1A2E; min-width:160px; text-align:center; }
.btn         { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:1px solid transparent; text-decoration:none; transition:all .15s; }
.btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
.btn-outline:hover { background:#f3f4f6; }
.btn-primary { background:var(--primary,#1d4ed8); color:#fff; border-color:var(--primary,#1d4ed8); }
.btn-sm      { padding:.35rem .7rem; font-size:.8rem; }

/* Stats */
.stats-grid  { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
@media(max-width:768px){ .stats-grid { grid-template-columns:repeat(2,1fr); } }
.stat-card   { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:1rem 1.25rem; }
.stat-value  { font-size:1.8rem; font-weight:800; line-height:1; }
.stat-label  { font-size:.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; margin-top:.25rem; }

/* Layout with sidebar */
.cal-layout  { display:grid; grid-template-columns:1fr 260px; gap:1.25rem; align-items:start; }
@media(max-width:900px){ .cal-layout { grid-template-columns:1fr; } }

/* Calendar */
.calendar    { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
.cal-head    { display:grid; grid-template-columns:repeat(7,1fr); background:#f8fafc; border-bottom:1px solid #e5e7eb; }
.cal-weekday { padding:.6rem .5rem; text-align:center; font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; }
.cal-body    { display:grid; grid-template-columns:repeat(7,1fr); }
.cal-day     { min-height:100px; border-right:1px solid #f0f0f0; border-bottom:1px solid #f0f0f0; padding:.4rem .35rem; position:relative; vertical-align:top; }
.cal-day:nth-child(7n) { border-right:none; }
.cal-day.other-month { background:#fafafa; }
.cal-day.today { background:#eff6ff; }
.cal-day-num { font-size:.78rem; font-weight:700; color:#374151; margin-bottom:.3rem; display:flex; align-items:center; justify-content:space-between; }
.cal-day.today .cal-day-num { color:#1d4ed8; }
.cal-add-btn { opacity:0; transition:opacity .15s; background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; line-height:1; font-size:14px; }
.cal-day:hover .cal-add-btn { opacity:1; }

/* Pills */
.cal-pills   { display:flex; flex-direction:column; gap:2px; }
.cal-pill    { display:flex; align-items:center; gap:.3rem; padding:.18rem .45rem; border-radius:999px; font-size:.7rem; font-weight:600; text-decoration:none; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; transition:opacity .15s; }
.cal-pill:hover { opacity:.85; }
.pill-blue   { background:#dbeafe; color:#1e40af; }
.pill-pink   { background:#fce7f3; color:#9d174d; }
.pill-green  { background:#d1fae5; color:#065f46; }
.pill-purple { background:#ede9fe; color:#5b21b6; }
.pill-dot    { width:5px; height:5px; border-radius:50%; flex-shrink:0; }
.dot-blue   { background:#1d4ed8; }
.dot-pink   { background:#ec4899; }
.dot-green  { background:#10b981; }
.dot-purple { background:#7c3aed; }
.pill-title { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; min-width:0; }

/* More indicator */
.cal-more    { font-size:.65rem; color:#6b7280; padding:.1rem .3rem; cursor:pointer; text-align:center; }

/* Sidebar */
.sidebar-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; margin-bottom:1rem; }
.sidebar-card-head { padding:.85rem 1rem; border-bottom:1px solid #e5e7eb; font-size:.85rem; font-weight:700; color:#0C1A2E; display:flex; align-items:center; gap:.4rem; }
.sidebar-card-body { padding:.75rem 1rem; }
.upcoming-item { display:flex; align-items:flex-start; gap:.6rem; padding:.55rem 0; border-bottom:1px solid #f3f4f6; text-decoration:none; }
.upcoming-item:last-child { border-bottom:none; padding-bottom:0; }
.upcoming-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:.3rem; }
.upcoming-content { flex:1; min-width:0; }
.upcoming-title { font-size:.8rem; font-weight:600; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.upcoming-meta  { font-size:.72rem; color:#6b7280; margin-top:.1rem; }

/* Legend */
.legend      { display:flex; flex-wrap:wrap; gap:.75rem; padding:.75rem 1rem; border-top:1px solid #f3f4f6; }
.legend-item { display:flex; align-items:center; gap:.35rem; font-size:.75rem; color:#6b7280; }

/* Create dropdown */
.create-menu-wrap { position:relative; display:inline-block; }
.create-menu { position:absolute; top:calc(100% + 4px); right:0; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:.4rem 0; min-width:200px; z-index:50; display:none; }
.create-menu.show { display:block; }
.create-menu a { display:flex; align-items:center; gap:.5rem; padding:.55rem 1rem; font-size:.85rem; color:#374151; text-decoration:none; }
.create-menu a:hover { background:#f9fafb; }
.create-menu-dot { width:8px; height:8px; border-radius:50%; }
</style>

@php
    $carbon = \Carbon\Carbon::parse($month . '-01');
    $prevMonth = $carbon->copy()->subMonth()->format('Y-m');
    $nextMonth = $carbon->copy()->addMonth()->format('Y-m');
    $todayStr  = now()->format('Y-m-d');

    // Build 7-col grid (starting Monday)
    $firstDayOfMonth = $carbon->copy()->startOfMonth();
    $startOfGrid     = $firstDayOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $lastDayOfMonth  = $carbon->copy()->endOfMonth();
    $endOfGrid       = $lastDayOfMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

    $gridDays = [];
    for ($d = $startOfGrid->copy(); $d->lte($endOfGrid); $d->addDay()) {
        $gridDays[] = $d->copy();
    }
@endphp

<div class="page-header">
    <div>
        <h2 class="page-title">&#128197; Calendario Social</h2>
        <p style="font-size:.83rem;color:#6b7280;margin:.25rem 0 0;">Gestión unificada de contenido programado</p>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <div class="month-nav">
            <a href="{{ route('admin.social.calendar', ['month' => $prevMonth]) }}" class="btn btn-outline btn-sm">&#8592;</a>
            <span class="month-label">{{ $carbon->isoFormat('MMMM YYYY') }}</span>
            <a href="{{ route('admin.social.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline btn-sm">&#8594;</a>
            <a href="{{ route('admin.social.calendar') }}" class="btn btn-outline btn-sm">Hoy</a>
        </div>
        <div class="create-menu-wrap">
            <button class="btn btn-primary btn-sm" onclick="toggleCreateMenu(this)">+ Crear</button>
            <div class="create-menu" id="createMenu">
                <a href="{{ route('admin.facebook.create') }}"><span class="create-menu-dot" style="background:#1d4ed8"></span> Post Facebook</a>
                <a href="{{ route('admin.carousels.create') }}"><span class="create-menu-dot" style="background:#ec4899"></span> Carrusel IG</a>
                <a href="{{ route('admin.social.stories.create') }}"><span class="create-menu-dot" style="background:#7c3aed"></span> Historia</a>
                <a href="{{ route('admin.posts.create') }}"><span class="create-menu-dot" style="background:#10b981"></span> Post Blog</a>
            </div>
        </div>
        <a href="{{ route('admin.social.upcoming') }}" class="btn btn-outline btn-sm">&#128337; Próximas</a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value" style="color:#0C1A2E;">{{ $stats['total'] }}</div>
        <div class="stat-label">Total este mes</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#10b981;">{{ $stats['published'] }}</div>
        <div class="stat-label">Publicados</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#7c3aed;">{{ $stats['scheduled'] }}</div>
        <div class="stat-label">Programados</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#f59e0b;">{{ $stats['draft'] }}</div>
        <div class="stat-label">Borradores</div>
    </div>
</div>

<div class="cal-layout">
    {{-- Calendar --}}
    <div>
        <div class="calendar">
            {{-- Weekday headers --}}
            <div class="cal-head">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $wd)
                <div class="cal-weekday">{{ $wd }}</div>
                @endforeach
            </div>

            {{-- Days grid --}}
            <div class="cal-body">
                @foreach($gridDays as $day)
                @php
                    $dayStr      = $day->format('Y-m-d');
                    $isThisMonth = $day->month === $carbon->month;
                    $isToday     = $dayStr === $todayStr;
                    $dayItems    = $calendarDays[$dayStr] ?? [];
                    $maxVisible  = 3;
                    $visibleItems = array_slice($dayItems, 0, $maxVisible);
                    $moreCount   = count($dayItems) - $maxVisible;
                @endphp
                <div class="cal-day {{ !$isThisMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                    <div class="cal-day-num">
                        <span>{{ $day->day }}</span>
                        <button class="cal-add-btn" onclick="openCreateForDay('{{ $dayStr }}')" title="Crear contenido para este día">+</button>
                    </div>
                    <div class="cal-pills">
                        @foreach($visibleItems as $item)
                        <a href="{{ $item['url'] }}" class="cal-pill pill-{{ $item['type_color'] }}" title="{{ $item['title'] }}">
                            <span class="pill-dot dot-{{ $item['type_color'] }}"></span>
                            <span class="pill-title">{{ $item['title'] }}</span>
                        </a>
                        @endforeach
                        @if($moreCount > 0)
                        <div class="cal-more">+{{ $moreCount }} más</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="legend">
                <div class="legend-item"><span class="pill-dot dot-blue"></span> Post Facebook</div>
                <div class="legend-item"><span class="pill-dot dot-pink"></span> Carrusel IG</div>
                <div class="legend-item"><span class="pill-dot dot-green"></span> Blog</div>
                <div class="legend-item"><span class="pill-dot dot-purple"></span> Historia</div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Upcoming --}}
        <div class="sidebar-card">
            <div class="sidebar-card-head">&#128337; Próximamente</div>
            <div class="sidebar-card-body" style="padding:0 1rem;">
                @forelse($upcomingItems as $item)
                <a href="{{ $item['url'] }}" class="upcoming-item">
                    <span class="upcoming-dot" style="background:
                        @if($item['type_color']==='blue') #1d4ed8
                        @elseif($item['type_color']==='pink') #ec4899
                        @elseif($item['type_color']==='green') #10b981
                        @elseif($item['type_color']==='purple') #7c3aed
                        @else #6b7280 @endif
                    "></span>
                    <div class="upcoming-content">
                        <div class="upcoming-title">{{ Str::limit($item['title'], 40) }}</div>
                        <div class="upcoming-meta">
                            {{ $item['type_label'] }}
                            · {{ \Carbon\Carbon::parse($item['date'])->isoFormat('D MMM H:mm') }}
                        </div>
                    </div>
                </a>
                @empty
                <p style="font-size:.8rem;color:#9ca3af;padding:.75rem 0;margin:0;">No hay contenido programado.</p>
                @endforelse
                @if(count($upcomingItems) >= 5)
                <div style="padding:.5rem 0;border-top:1px solid #f3f4f6;">
                    <a href="{{ route('admin.social.upcoming') }}" style="font-size:.78rem;color:#1d4ed8;font-weight:600;">Ver todas →</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick create links --}}
        <div class="sidebar-card">
            <div class="sidebar-card-head">&#9998; Crear contenido</div>
            <div class="sidebar-card-body" style="display:flex;flex-direction:column;gap:.4rem;">
                <a href="{{ route('admin.facebook.create') }}" class="btn btn-outline btn-sm" style="justify-content:flex-start;gap:.5rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#1d4ed8;flex-shrink:0;"></span> Post Facebook
                </a>
                <a href="{{ route('admin.carousels.create') }}" class="btn btn-outline btn-sm" style="justify-content:flex-start;gap:.5rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#ec4899;flex-shrink:0;"></span> Carrusel IG
                </a>
                <a href="{{ route('admin.social.stories.create') }}" class="btn btn-outline btn-sm" style="justify-content:flex-start;gap:.5rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#7c3aed;flex-shrink:0;"></span> Historia
                </a>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-outline btn-sm" style="justify-content:flex-start;gap:.5rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#10b981;flex-shrink:0;"></span> Post Blog
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Create-for-day modal (simple) --}}
<div id="dayCreateModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;padding:1.5rem;width:320px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="margin:0 0 1rem;font-size:1rem;font-weight:700;">Crear para <span id="dayCreateLabel"></span></h3>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            <a id="dayLinkFb"       href="#" class="btn btn-outline" style="justify-content:flex-start;gap:.5rem;"><span style="width:8px;height:8px;border-radius:50%;background:#1d4ed8;"></span> Post Facebook</a>
            <a id="dayLinkCarousel" href="#" class="btn btn-outline" style="justify-content:flex-start;gap:.5rem;"><span style="width:8px;height:8px;border-radius:50%;background:#ec4899;"></span> Carrusel IG</a>
            <a id="dayLinkStory"    href="#" class="btn btn-outline" style="justify-content:flex-start;gap:.5rem;"><span style="width:8px;height:8px;border-radius:50%;background:#7c3aed;"></span> Historia</a>
            <a id="dayLinkBlog"     href="#" class="btn btn-outline" style="justify-content:flex-start;gap:.5rem;"><span style="width:8px;height:8px;border-radius:50%;background:#10b981;"></span> Post Blog</a>
        </div>
        <div style="margin-top:1rem;text-align:right;">
            <button onclick="document.getElementById('dayCreateModal').style.display='none'" class="btn btn-outline btn-sm">Cancelar</button>
        </div>
    </div>
</div>

<script>
function toggleCreateMenu(btn) {
    const menu = document.getElementById('createMenu');
    menu.classList.toggle('show');
    document.addEventListener('click', function closeMenu(e) {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('show');
            document.removeEventListener('click', closeMenu);
        }
    });
}

function openCreateForDay(dayStr) {
    const modal = document.getElementById('dayCreateModal');
    document.getElementById('dayCreateLabel').textContent = dayStr;
    document.getElementById('dayLinkFb').href        = '{{ route("admin.facebook.create") }}';
    document.getElementById('dayLinkCarousel').href  = '{{ route("admin.carousels.create") }}';
    document.getElementById('dayLinkStory').href     = '{{ route("admin.social.stories.create") }}';
    document.getElementById('dayLinkBlog').href      = '{{ route("admin.posts.create") }}';
    modal.style.display = 'flex';
}

document.getElementById('dayCreateModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endsection
