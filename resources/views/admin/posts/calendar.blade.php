@extends('layouts.app-sidebar')
@section('title', 'Calendario de Contenido')

@section('styles')
<style>
    .cal-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .cal-nav { display: flex; align-items: center; gap: 0.5rem; }
    .cal-nav-btn { width: 34px; height: 34px; border: 1px solid var(--border); border-radius: var(--radius); background: var(--card); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; color: var(--text); }
    .cal-nav-btn:hover { border-color: var(--primary); color: var(--primary); }
    .cal-title { font-size: 1.1rem; font-weight: 700; min-width: 180px; text-align: center; text-transform: capitalize; }
    .cal-views { display: flex; gap: 0.25rem; background: var(--bg); border-radius: var(--radius); padding: 3px; }
    .cal-view-btn { padding: 0.35rem 0.85rem; border: none; border-radius: calc(var(--radius) - 2px); background: transparent; font-size: 0.78rem; font-weight: 600; cursor: pointer; color: var(--text-muted); transition: all 0.15s; }
    .cal-view-btn.active { background: var(--primary); color: #fff; }

    /* Month grid */
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--border); gap: 1px; }
    .cal-header-cell { padding: 0.5rem 0.25rem; text-align: center; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: var(--bg); color: var(--text-muted); }
    .cal-cell { min-height: 110px; max-height: 130px; padding: 0.35rem; background: var(--card); position: relative; transition: background 0.15s; overflow: hidden; }
    .cal-cell.today { background: rgba(102,126,234,0.04); }
    .cal-cell.other-month { opacity: 0.35; }
    .cal-cell.drag-over { background: rgba(102,126,234,0.08); outline: 2px dashed var(--primary); outline-offset: -2px; }
    .cal-date { font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem; padding: 0.1rem 0.3rem; color: var(--text-muted); }
    .cal-cell.today .cal-date { color: #fff; background: var(--primary); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; }

    /* Post chips */
    .cal-post { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; margin-bottom: 2px; cursor: grab; overflow: hidden; text-overflow: ellipsis; border-left: 3px solid; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal; word-break: break-word; text-decoration: none; color: inherit; transition: all 0.1s; line-height: 1.3; max-height: 2.6em; }
    .cal-post:hover { filter: brightness(0.95); transform: scale(1.02); }
    .cal-post.dragging { opacity: 0.4; }

    /* Status colors — shared by all views */
    .status-published { border-left-color: #10b981; background: #ecfdf5; color: #065f46; }
    .status-scheduled { border-left-color: #3b82f6; background: #eef2ff; color: #3730a3; }
    .status-draft { border-left-color: #f59e0b; background: #fffbeb; color: #92400e; }

    .cal-more { font-size: 0.65rem; color: var(--text-muted); padding: 0.1rem 0.3rem; cursor: pointer; }
    .cal-more:hover { color: var(--primary); }

    /* Week view */
    .cal-week { display: grid; grid-template-columns: repeat(7, 1fr); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; background: var(--border); gap: 1px; }
    .cal-week-col { background: var(--card); min-height: 300px; }
    .cal-week-header { padding: 0.5rem; text-align: center; background: var(--bg); border-bottom: 1px solid var(--border); }
    .cal-week-header .day-name { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
    .cal-week-header .day-num { font-size: 1.1rem; font-weight: 700; margin-top: 0.15rem; }
    .cal-week-header.today .day-num { color: var(--primary); }
    .cal-week-body { padding: 0.35rem; }
    .cal-week-post { font-size: 0.75rem; padding: 0.4rem 0.5rem; border-radius: 6px; margin-bottom: 0.35rem; border-left: 3px solid; cursor: grab; text-decoration: none; display: block; color: inherit; }
    .cal-week-post .post-time { font-size: 0.65rem; color: var(--text-muted); display: block; margin-bottom: 0.15rem; }

    /* Day view */
    .cal-day-header { text-align: center; padding: 1rem; background: var(--bg); border-radius: var(--radius); margin-bottom: 1rem; }
    .cal-day-header .day-full { font-size: 1.25rem; font-weight: 700; text-transform: capitalize; }
    .cal-day-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .cal-day-item { display: flex; gap: 1rem; padding: 1rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); border-left: 4px solid; text-decoration: none; color: inherit; transition: all 0.15s; }
    .cal-day-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .cal-day-item .item-time { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); min-width: 50px; }
    .cal-day-item .item-title { font-weight: 600; font-size: 0.9rem; }
    .cal-day-item .item-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem; }

    /* Drafts panel */
    .cal-layout { display: grid; grid-template-columns: 1fr 240px; gap: 1.25rem; align-items: start; }
    .drafts-panel { position: sticky; top: 72px; }
    .drafts-panel .drafts-list { max-height: 500px; overflow-y: auto; }
    .draft-item { font-size: 0.78rem; padding: 0.5rem 0.6rem; border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.35rem; cursor: grab; background: var(--card); border-left: 3px solid #f59e0b; transition: all 0.1s; }
    .draft-item:hover { border-color: var(--primary); }
    .draft-item.dragging { opacity: 0.4; }
    .draft-title { font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .draft-meta { font-size: 0.68rem; color: var(--text-muted); margin-top: 0.15rem; }

    /* Legend */
    .cal-legend { display: flex; gap: 1rem; font-size: 0.72rem; color: var(--text-muted); }
    .cal-legend-item { display: flex; align-items: center; gap: 0.3rem; }
    .cal-legend-dot { width: 10px; height: 10px; border-radius: 2px; }

    @media (max-width: 1024px) { .cal-layout { grid-template-columns: 1fr; } .drafts-panel { position: static; } }
    @media (max-width: 768px) {
        .cal-cell { min-height: 60px; }
        .cal-post { font-size: 0.6rem; padding: 0.15rem 0.25rem; }
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Calendario de Contenido</h2>
        <p class="text-muted">Programa y organiza tus publicaciones del blog</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">Lista de Posts</a>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">+ Nuevo Post</a>
    </div>
</div>

<div class="cal-toolbar">
    <div class="cal-nav">
        <button class="cal-nav-btn" onclick="calNav(-1)" title="Anterior">&lsaquo;</button>
        <span class="cal-title" id="calTitle"></span>
        <button class="cal-nav-btn" onclick="calNav(1)" title="Siguiente">&rsaquo;</button>
        <button class="btn btn-sm btn-outline" onclick="calToday()" style="margin-left:0.5rem;">Hoy</button>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
        <div class="cal-legend">
            <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#10b981;"></span> Publicado</span>
            <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#3b82f6;"></span> Programado</span>
            <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#f59e0b;"></span> Borrador</span>
        </div>
        <div class="cal-views">
            <button class="cal-view-btn active" data-view="month" onclick="setView('month')">Mes</button>
            <button class="cal-view-btn" data-view="week" onclick="setView('week')">Semana</button>
            <button class="cal-view-btn" data-view="day" onclick="setView('day')">Dia</button>
        </div>
    </div>
</div>

<div class="cal-layout">
    <div>
        <div id="calMonth"></div>
        <div id="calWeek" style="display:none;"></div>
        <div id="calDay" style="display:none;"></div>
    </div>

    <div class="drafts-panel">
        <div class="card">
            <div class="card-header"><h3>Borradores sin fecha</h3></div>
            <div class="card-body" style="padding:0.5rem;">
                <div class="drafts-list" id="draftsList"></div>
                <div id="draftsEmpty" class="text-muted text-center" style="font-size:0.78rem;padding:1rem;display:none;">
                    Sin borradores pendientes
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
var CS = {
    view: localStorage.getItem('cal_view') || 'month',
    date: new Date({{ $currentDate->year }}, {{ $currentDate->month - 1 }}, {{ now()->day }}),
    posts: @json($postsJson),
    drafts: @json($draftsJson),
    token: document.querySelector('meta[name="csrf-token"]').content,
};

var MONTHS = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
var DAYS = ['Lun','Mar','Mie','Jue','Vie','Sab','Dom'];

function pad(n) { return n < 10 ? '0' + n : n; }
function fmtDate(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
function sameDay(a, b) { return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); }
function todayStr() { var t = new Date(); return fmtDate(t); }

function setView(v) {
    CS.view = v;
    localStorage.setItem('cal_view', v);
    document.querySelectorAll('.cal-view-btn').forEach(function(b) { b.classList.toggle('active', b.dataset.view === v); });
    document.getElementById('calMonth').style.display = v === 'month' ? '' : 'none';
    document.getElementById('calWeek').style.display = v === 'week' ? '' : 'none';
    document.getElementById('calDay').style.display = v === 'day' ? '' : 'none';
    render();
}

function calNav(dir) {
    if (CS.view === 'month') CS.date.setMonth(CS.date.getMonth() + dir);
    else if (CS.view === 'week') CS.date.setDate(CS.date.getDate() + dir * 7);
    else CS.date.setDate(CS.date.getDate() + dir);
    fetchAndRender();
}

function calToday() { CS.date = new Date(); fetchAndRender(); }

function updateTitle() {
    var el = document.getElementById('calTitle');
    if (CS.view === 'month') {
        el.textContent = MONTHS[CS.date.getMonth()] + ' ' + CS.date.getFullYear();
    } else if (CS.view === 'week') {
        var start = getWeekStart(CS.date);
        var end = new Date(start); end.setDate(end.getDate() + 6);
        el.textContent = pad(start.getDate()) + ' - ' + pad(end.getDate()) + ' ' + MONTHS[end.getMonth()] + ' ' + end.getFullYear();
    } else {
        el.textContent = DAYS[(CS.date.getDay() + 6) % 7] + ' ' + CS.date.getDate() + ' ' + MONTHS[CS.date.getMonth()] + ' ' + CS.date.getFullYear();
    }
}

function getWeekStart(d) { var day = d.getDay(); var diff = (day === 0 ? -6 : 1) - day; var s = new Date(d); s.setDate(s.getDate() + diff); return s; }

function postsForDate(dateStr) { return CS.posts.filter(function(p) { return p.date === dateStr; }); }

function chipClass(status) { return 'status-' + status; }

function dragStart(e) {
    e.dataTransfer.setData('text/plain', e.currentTarget.dataset.id);
    e.dataTransfer.effectAllowed = 'move';
    e.currentTarget.classList.add('dragging');
}
function dragEnd(e) { e.currentTarget.classList.remove('dragging'); }
function dragOver(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); }
function dragLeave(e) { e.currentTarget.classList.remove('drag-over'); }
function drop(e, dateStr) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    var id = e.dataTransfer.getData('text/plain');
    if (!id) return;
    moveTo(id, dateStr + ' 09:00:00');
}

function moveTo(id, datetime) {
    fetch('{{ url("admin/content-calendar") }}/' + id + '/date', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CS.token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ published_at: datetime })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            // Remove from drafts if it was there
            CS.drafts = CS.drafts.filter(function(d) { return d.id != id; });
            // Update or add in posts
            var existing = CS.posts.find(function(p) { return p.id == id; });
            if (existing) {
                existing.date = data.post.date;
                existing.time = data.post.time;
                existing.status = data.post.status;
            } else {
                var draft = CS.drafts.find(function(d) { return d.id == id; }) || {};
                CS.posts.push({ id: data.post.id, title: data.post.title, date: data.post.date, time: data.post.time, status: data.post.status, category: null, url: '{{ url("admin/posts") }}/' + id + '/edit' });
            }
            render();
        }
    });
}

// ── MONTH VIEW ──
function renderMonth() {
    var year = CS.date.getFullYear(), month = CS.date.getMonth();
    var first = new Date(year, month, 1);
    var startDay = (first.getDay() + 6) % 7;
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var prevDays = new Date(year, month, 0).getDate();
    var today = todayStr();

    var html = '<div class="cal-grid">';
    DAYS.forEach(function(d) { html += '<div class="cal-header-cell">' + d + '</div>'; });

    var totalCells = Math.ceil((startDay + daysInMonth) / 7) * 7;
    for (var i = 0; i < totalCells; i++) {
        var dayNum, dateStr, cls = 'cal-cell';
        if (i < startDay) {
            dayNum = prevDays - startDay + i + 1;
            var pm = month === 0 ? 11 : month - 1;
            var py = month === 0 ? year - 1 : year;
            dateStr = py + '-' + pad(pm + 1) + '-' + pad(dayNum);
            cls += ' other-month';
        } else if (i >= startDay + daysInMonth) {
            dayNum = i - startDay - daysInMonth + 1;
            var nm = month === 11 ? 0 : month + 1;
            var ny = month === 11 ? year + 1 : year;
            dateStr = ny + '-' + pad(nm + 1) + '-' + pad(dayNum);
            cls += ' other-month';
        } else {
            dayNum = i - startDay + 1;
            dateStr = year + '-' + pad(month + 1) + '-' + pad(dayNum);
        }
        if (dateStr === today) cls += ' today';

        html += '<div class="' + cls + '" ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="drop(event,\'' + dateStr + '\')">';
        html += '<div class="cal-date">' + dayNum + '</div>';

        var dayPosts = postsForDate(dateStr);
        var show = Math.min(dayPosts.length, 3);
        for (var j = 0; j < show; j++) {
            var p = dayPosts[j];
            html += '<a href="' + p.url + '" class="cal-post ' + chipClass(p.status) + '" draggable="true" data-id="' + p.id + '" ondragstart="dragStart(event)" ondragend="dragEnd(event)" title="' + p.title.replace(/"/g, '&quot;') + '">' + p.time + ' ' + p.title + '</a>';
        }
        if (dayPosts.length > 3) {
            html += '<span class="cal-more" onclick="CS.date=new Date(\'' + dateStr + '\');setView(\'day\');">+' + (dayPosts.length - 3) + ' mas</span>';
        }
        html += '</div>';
    }
    html += '</div>';
    document.getElementById('calMonth').innerHTML = html;
}

// ── WEEK VIEW ──
function renderWeek() {
    var start = getWeekStart(CS.date);
    var today = todayStr();
    var html = '<div class="cal-week">';

    for (var i = 0; i < 7; i++) {
        var d = new Date(start); d.setDate(d.getDate() + i);
        var dateStr = fmtDate(d);
        var isToday = dateStr === today;

        html += '<div class="cal-week-col" ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="drop(event,\'' + dateStr + '\')">';
        html += '<div class="cal-week-header' + (isToday ? ' today' : '') + '">';
        html += '<div class="day-name">' + DAYS[i] + '</div>';
        html += '<div class="day-num">' + d.getDate() + '</div>';
        html += '</div>';
        html += '<div class="cal-week-body">';

        postsForDate(dateStr).forEach(function(p) {
            html += '<a href="' + p.url + '" class="cal-week-post ' + chipClass(p.status) + '" draggable="true" data-id="' + p.id + '" ondragstart="dragStart(event)" ondragend="dragEnd(event)">';
            html += '<span class="post-time">' + p.time + '</span>';
            html += p.title;
            html += '</a>';
        });
        html += '</div></div>';
    }
    html += '</div>';
    document.getElementById('calWeek').innerHTML = html;
}

// ── DAY VIEW ──
function renderDay() {
    var dateStr = fmtDate(CS.date);
    var dayPosts = postsForDate(dateStr).sort(function(a, b) { return a.time.localeCompare(b.time); });

    var html = '<div class="cal-day-header" ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="drop(event,\'' + dateStr + '\')">';
    html += '<div class="day-full">' + DAYS[(CS.date.getDay() + 6) % 7] + ' ' + CS.date.getDate() + ' de ' + MONTHS[CS.date.getMonth()] + ', ' + CS.date.getFullYear() + '</div>';
    html += '<div class="text-muted" style="font-size:0.82rem;margin-top:0.25rem;">' + dayPosts.length + ' publicacion' + (dayPosts.length !== 1 ? 'es' : '') + '</div>';
    html += '</div>';

    if (dayPosts.length === 0) {
        html += '<div class="text-center text-muted" style="padding:3rem;"><p>No hay posts para este dia.</p><p style="margin-top:0.5rem;">Arrastra un borrador aqui o <a href="{{ route("admin.posts.create") }}" style="color:var(--primary);font-weight:500;">crea uno nuevo</a>.</p></div>';
    } else {
        html += '<div class="cal-day-list">';
        dayPosts.forEach(function(p) {
            html += '<a href="' + p.url + '" class="cal-day-item ' + chipClass(p.status) + '" draggable="true" data-id="' + p.id + '" ondragstart="dragStart(event)" ondragend="dragEnd(event)">';
            html += '<div class="item-time">' + p.time + '</div>';
            html += '<div><div class="item-title">' + p.title + '</div>';
            html += '<div class="item-meta">' + (p.category || 'Sin categoria') + ' &middot; ' + (p.status === 'published' ? 'Publicado' : p.status === 'scheduled' ? 'Programado' : 'Borrador') + '</div></div>';
            html += '</a>';
        });
        html += '</div>';
    }
    document.getElementById('calDay').innerHTML = html;
}

// ── DRAFTS PANEL ──
function renderDrafts() {
    var el = document.getElementById('draftsList');
    var empty = document.getElementById('draftsEmpty');
    if (CS.drafts.length === 0) {
        el.innerHTML = '';
        empty.style.display = '';
        return;
    }
    empty.style.display = 'none';
    var html = '';
    CS.drafts.forEach(function(d) {
        html += '<div class="draft-item" draggable="true" data-id="' + d.id + '" ondragstart="dragStart(event)" ondragend="dragEnd(event)">';
        html += '<div class="draft-title">' + d.title + '</div>';
        html += '<div class="draft-meta"><a href="' + d.url + '" style="color:var(--primary);">Editar</a></div>';
        html += '</div>';
    });
    el.innerHTML = html;
}

function render() {
    updateTitle();
    if (CS.view === 'month') renderMonth();
    else if (CS.view === 'week') renderWeek();
    else renderDay();
    renderDrafts();
}

function fetchAndRender() {
    var start, end;
    if (CS.view === 'month') {
        var first = new Date(CS.date.getFullYear(), CS.date.getMonth(), 1);
        start = getWeekStart(first);
        end = new Date(CS.date.getFullYear(), CS.date.getMonth() + 1, 0);
        var endDay = end.getDay(); if (endDay !== 0) end.setDate(end.getDate() + (7 - endDay));
    } else if (CS.view === 'week') {
        start = getWeekStart(CS.date);
        end = new Date(start); end.setDate(end.getDate() + 6);
    } else {
        start = new Date(CS.date); end = new Date(CS.date);
    }

    fetch('{{ route("admin.content-calendar.events") }}?start=' + fmtDate(start) + '&end=' + fmtDate(end), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        CS.posts = data.posts;
        CS.drafts = data.drafts;
        render();
    });
}

// Init
setView(CS.view);
</script>
@endsection
