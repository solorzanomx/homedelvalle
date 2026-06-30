@if ($paginator->hasPages())
<nav style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; font-size:0.82rem;">

    <span style="color:var(--text-muted, #6b7280);">
        Mostrando
        @if($paginator->firstItem())
            <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
        @else
            {{ $paginator->count() }}
        @endif
        de <strong>{{ $paginator->total() }}</strong>
    </span>

    <div style="display:flex; gap:0.2rem; align-items:center;">

        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--border,#e5e7eb);color:var(--text-muted,#9ca3af);cursor:not-allowed;">&#8249;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--border,#e5e7eb);color:var(--text,#111);text-decoration:none;transition:all .15s;"
               onmouseover="this.style.borderColor='var(--primary,#6366f1)';this.style.color='var(--primary,#6366f1)'"
               onmouseout="this.style.borderColor='var(--border,#e5e7eb)';this.style.color='var(--text,#111)'">&#8249;</a>
        @endif

        {{-- Páginas --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;color:var(--text-muted,#9ca3af);">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--primary,#6366f1);background:var(--primary,#6366f1);color:#fff;font-weight:700;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--border,#e5e7eb);color:var(--text,#111);text-decoration:none;transition:all .15s;"
                           onmouseover="this.style.borderColor='var(--primary,#6366f1)';this.style.color='var(--primary,#6366f1)'"
                           onmouseout="this.style.borderColor='var(--border,#e5e7eb)';this.style.color='var(--text,#111)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--border,#e5e7eb);color:var(--text,#111);text-decoration:none;transition:all .15s;"
               onmouseover="this.style.borderColor='var(--primary,#6366f1)';this.style.color='var(--primary,#6366f1)'"
               onmouseout="this.style.borderColor='var(--border,#e5e7eb)';this.style.color='var(--text,#111)'">&#8250;</a>
        @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid var(--border,#e5e7eb);color:var(--text-muted,#9ca3af);cursor:not-allowed;">&#8250;</span>
        @endif

    </div>
</nav>
@endif
