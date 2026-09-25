{{-- Visor a pantalla completa de documentos + aprobar/rechazar sin recargar --}}
<style>
.doc-thumb { width:44px; height:44px; object-fit:cover; border-radius:6px; border:1px solid var(--border); flex-shrink:0; cursor:pointer; background:#f1f5f9; }
.doc-more { position:relative; display:inline-block; }
.doc-more > summary { list-style:none; cursor:pointer; }
.doc-more > summary::-webkit-details-marker { display:none; }
.doc-more-menu { position:absolute; right:0; top:100%; margin-top:4px; background:var(--card); border:1px solid var(--border); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.15); z-index:20; min-width:140px; padding:.25rem; }
.doc-more-menu button { width:100%; text-align:left; background:none; border:0; padding:.5rem .65rem; font-size:.8rem; cursor:pointer; border-radius:6px; }
.doc-more-menu button:hover { background:#f1f5f9; }

#hdvViewer { position:fixed; inset:0; background:rgba(15,23,42,.92); z-index:9999; display:none; flex-direction:column; }
#hdvViewer.open { display:flex; }
.hv-top { display:flex; align-items:center; gap:.75rem; padding:.7rem 1rem; color:#fff; }
.hv-top .hv-title { flex:1; min-width:0; font-weight:700; font-size:.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hv-top .hv-count { font-size:.8rem; opacity:.7; }
.hv-btn { background:rgba(255,255,255,.12); color:#fff; border:0; border-radius:8px; padding:.45rem .8rem; font-size:.85rem; cursor:pointer; }
.hv-btn:hover { background:rgba(255,255,255,.22); }
.hv-body { flex:1; display:flex; min-height:0; }
.hv-stage { flex:1; position:relative; overflow:auto; display:flex; align-items:center; justify-content:center; padding:1rem; }
.hv-stage img { max-width:100%; max-height:100%; transition:transform .15s; cursor:zoom-in; transform-origin:center center; }
.hv-stage img.zoomed { max-width:none; max-height:none; cursor:zoom-out; }
.hv-stage iframe { width:100%; height:100%; border:0; background:#fff; border-radius:6px; }
.hv-nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(255,255,255,.15); color:#fff; border:0; width:44px; height:64px; font-size:1.6rem; cursor:pointer; border-radius:10px; z-index:2; }
.hv-nav:hover { background:rgba(255,255,255,.3); }
.hv-nav.prev { left:.75rem; } .hv-nav.next { right:.75rem; }
.hv-side { width:320px; background:#fff; padding:1.1rem; overflow-y:auto; display:flex; flex-direction:column; gap:.8rem; }
.hv-side h3 { margin:0; font-size:1rem; }
.hv-side .hv-meta { font-size:.78rem; color:#64748b; word-break:break-all; }
.hv-ai { font-size:.8rem; padding:.55rem .7rem; border-radius:8px; background:#f8fafc; border:1px solid #e2e8f0; }
.hv-actions { display:flex; flex-direction:column; gap:.5rem; margin-top:auto; }
.hv-approve { background:#16a34a; color:#fff; border:0; border-radius:9px; padding:.75rem; font-size:.95rem; font-weight:700; cursor:pointer; }
.hv-reject-open { background:#fff; color:#dc2626; border:1.5px solid #dc2626; border-radius:9px; padding:.65rem; font-size:.9rem; font-weight:700; cursor:pointer; }
.hv-reject-box { display:none; flex-direction:column; gap:.5rem; }
.hv-reject-box.open { display:flex; }
.hv-chip { display:inline-block; border:1px solid #cbd5e1; background:#fff; border-radius:9999px; padding:.3rem .7rem; font-size:.75rem; cursor:pointer; margin:0 .3rem .3rem 0; }
.hv-chip:hover { background:#f1f5f9; }
.hv-reject-box textarea { width:100%; min-height:70px; border:1px solid #cbd5e1; border-radius:8px; padding:.5rem; font-size:.85rem; resize:vertical; }
.hv-confirm-reject { background:#dc2626; color:#fff; border:0; border-radius:9px; padding:.7rem; font-weight:700; cursor:pointer; }
.hv-hint { font-size:.7rem; color:#94a3b8; text-align:center; }
@media (max-width: 800px) { .hv-body { flex-direction:column; } .hv-side { width:auto; max-height:45vh; } }
</style>

<div id="hdvViewer" role="dialog" aria-modal="true">
    <div class="hv-top">
        <div class="hv-title" id="hvTitle"></div>
        <span class="hv-count" id="hvCount"></span>
        <span id="hvImgTools">
            <button class="hv-btn" type="button" onclick="hdvDocViewer.rotate()" title="Rotar">⟳ Rotar</button>
            <button class="hv-btn" type="button" onclick="hdvDocViewer.toggleZoom()" title="Zoom">🔍 Zoom</button>
        </span>
        <a class="hv-btn" id="hvOpenTab" href="#" target="_blank" rel="noopener" style="text-decoration:none;">Abrir en pestaña</a>
        <button class="hv-btn" type="button" onclick="hdvDocViewer.close()">✕ Cerrar</button>
    </div>
    <div class="hv-body">
        <div class="hv-stage" id="hvStage">
            <button class="hv-nav prev" type="button" onclick="hdvDocViewer.step(-1)" title="Anterior (←)">‹</button>
            <button class="hv-nav next" type="button" onclick="hdvDocViewer.step(1)" title="Siguiente (→)">›</button>
            <div id="hvContent" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"></div>
        </div>
        <aside class="hv-side">
            <div>
                <h3 id="hvSideTitle"></h3>
                <div class="hv-meta" id="hvSideFile"></div>
                <div class="hv-meta" id="hvSideMeta"></div>
            </div>
            <div><span class="badge" id="hvStatusBadge"></span></div>
            <div class="hv-ai" id="hvAi" style="display:none;"></div>
            <div class="hv-ai" id="hvQuality" style="display:none;background:#fffbeb;border-color:#fde68a;color:#92400e;"></div>
            <div class="hv-ai" id="hvReasonShown" style="display:none;background:#fef2f2;border-color:#fecaca;color:#991b1b;"></div>
            <div class="hv-actions">
                <div class="hv-reject-box" id="hvRejectBox">
                    <div>
                        <span class="hv-chip" data-reason="Está borroso o ilegible. Súbelo de nuevo con mejor luz y enfoque.">Ilegible</span>
                        <span class="hv-chip" data-reason="El documento está vencido o tiene más de 3 meses. Sube uno más reciente.">Vencido</span>
                        <span class="hv-chip" data-reason="El documento no corresponde al titular del trato.">No es del titular</span>
                        <span class="hv-chip" data-reason="Parece una foto tomada a una pantalla y no se lee bien. Descarga el PDF original (de tu banco o proveedor) y súbelo de nuevo, o tómale foto al documento sobre una mesa.">Foto de pantalla</span>
                        <span class="hv-chip" data-reason="Está incompleto (falta el reverso o alguna página). Súbelo completo.">Incompleto</span>
                        <span class="hv-chip" data-reason="No es el documento que se solicita en este apartado.">Documento equivocado</span>
                    </div>
                    <textarea id="hvReason" placeholder="Motivo que verá el cliente en su Portal…"></textarea>
                    <button class="hv-confirm-reject" type="button" onclick="hdvDocViewer.confirmReject()">Rechazar y avisar al cliente</button>
                </div>
                <button class="hv-approve" type="button" id="hvApprove" onclick="hdvDocViewer.setStatus(hdvDocViewer.currentId(), 'verified', true)">✓ Aprobar documento</button>
                <button class="hv-reject-open" type="button" id="hvRejectOpen" onclick="hdvDocViewer.showReject(true)">✗ Rechazar…</button>
                <div class="hv-hint">← → navegar · A aprobar · R rechazar · Esc cerrar</div>
            </div>
        </aside>
    </div>
</div>

<script>
window.hdvDocViewer = (function () {
    var csrf = '{{ csrf_token() }}';
    var statusUrl = "{{ url('documents') }}/";
    var idx = -1, rotation = 0, changed = false;
    var el = function (id) { return document.getElementById(id); };
    var badgeClass = { verified: 'badge-green', rejected: 'badge-red', received: 'badge-blue', pending: 'badge-yellow' };
    var statusLabel = { verified: 'Verificado', rejected: 'Rechazado', received: 'Recibido', pending: 'Pendiente' };

    function rows() {
        // Todas las filas de documento de la página (pestaña de la renta o bandeja central);
        // las ocultas por un filtro no cuentan para la navegación.
        return Array.prototype.slice.call(document.querySelectorAll('.doc-item[data-doc-id]')).filter(function (r) {
            return r.dataset.kind !== 'other' && !r.classList.contains('doc-filtered-out');
        });
    }
    function rowById(id) { return document.getElementById('docrow-' + id); }
    function current() { return rows()[idx]; }

    function paintRow(row, status, reason) {
        row.dataset.status = status; row.dataset.reason = reason || '';
        var b = row.querySelector('.doc-badge');
        b.className = 'badge ' + badgeClass[status] + ' doc-badge'; b.textContent = statusLabel[status];
        var ap = row.querySelector('.doc-approve'), rj = row.querySelector('.doc-reject');
        if (ap) ap.style.display = status === 'verified' ? 'none' : '';
        if (rj) rj.style.display = status === 'rejected' ? 'none' : '';
        var rs = row.querySelector('.doc-reason');
        rs.style.display = (status === 'rejected' && reason) ? '' : 'none';
        rs.querySelector('.doc-reason-text').textContent = reason || '';
    }

    function render() {
        var row = current(); if (!row) return;
        var d = row.dataset, all = rows();
        el('hvTitle').textContent = d.title;
        el('hvCount').textContent = (idx + 1) + ' de ' + all.length;
        el('hvSideTitle').textContent = d.title;
        el('hvSideFile').textContent = d.file;
        el('hvSideMeta').textContent = 'Subido: ' + d.meta;
        el('hvOpenTab').href = d.preview;
        var b = el('hvStatusBadge'); b.className = 'badge ' + badgeClass[d.status]; b.textContent = statusLabel[d.status];
        var ai = el('hvAi');
        if (d.ai) { ai.style.display = ''; ai.textContent = '🤖 Verificación automática: ' + d.ai; ai.style.color = (d.aiStatus === 'match') ? '#166534' : (d.aiStatus === 'mismatch' || d.aiStatus === 'expired') ? '#991b1b' : '#475569'; }
        else ai.style.display = 'none';
        var ql = el('hvQuality');
        if (d.quality) { ql.style.display = ''; ql.textContent = '⚠ Calidad dudosa: ' + d.quality; } else ql.style.display = 'none';
        var rs = el('hvReasonShown');
        if (d.status === 'rejected' && d.reason) { rs.style.display = ''; rs.textContent = 'Motivo enviado al cliente: ' + d.reason; } else rs.style.display = 'none';
        el('hvApprove').style.display = d.status === 'verified' ? 'none' : '';
        rotation = 0;
        var c = el('hvContent'); c.innerHTML = '';
        el('hvImgTools').style.display = d.kind === 'image' ? '' : 'none';
        if (d.kind === 'image') {
            var img = document.createElement('img'); img.src = d.preview; img.alt = d.title;
            img.onclick = toggleZoom; c.appendChild(img);
        } else {
            var f = document.createElement('iframe'); f.src = d.preview; c.appendChild(f);
        }
        el('hvStage').querySelector('.prev').style.display = all.length > 1 ? '' : 'none';
        el('hvStage').querySelector('.next').style.display = all.length > 1 ? '' : 'none';
    }

    function showReject(on) {
        el('hvRejectBox').classList.toggle('open', on);
        el('hvRejectOpen').style.display = on ? 'none' : '';
        el('hvApprove').style.display = on ? 'none' : (current() && current().dataset.status === 'verified' ? 'none' : '');
        if (on) { el('hvReason').value = ''; setTimeout(function () { el('hvReason').focus(); }, 30); }
    }

    function open(id, rejectMode) {
        var r = rows(); idx = r.findIndex(function (x) { return x.dataset.docId == id; });
        if (idx < 0) return;
        el('hdvViewer').classList.add('open'); document.body.style.overflow = 'hidden';
        render(); showReject(!!rejectMode);
    }
    function close() {
        el('hdvViewer').classList.remove('open'); document.body.style.overflow = ''; el('hvContent').innerHTML = '';
    }
    function step(n) {
        var all = rows(); if (!all.length) return;
        idx = (idx + n + all.length) % all.length; render(); showReject(false);
    }
    function nextPending() {
        var all = rows();
        for (var i = 1; i <= all.length; i++) {
            var j = (idx + i) % all.length;
            if (all[j].dataset.status === 'received' && j !== idx) return j;
        }
        return -1;
    }
    function openFirstPending() {
        var all = rows(), j = all.findIndex(function (r) { return r.dataset.status === 'received'; });
        if (j >= 0) open(all[j].dataset.docId);
    }
    function toggleZoom() {
        var img = el('hvContent').querySelector('img'); if (img) img.classList.toggle('zoomed');
    }
    function rotate() {
        var img = el('hvContent').querySelector('img'); if (!img) return;
        rotation = (rotation + 90) % 360; img.style.transform = 'rotate(' + rotation + 'deg)';
    }

    function setStatus(id, status, fromViewer, reason) {
        var row = rowById(id); if (!row) return;
        var body = new FormData(); body.append('_method', 'PATCH'); body.append('status', status);
        if (reason) body.append('rejection_reason', reason);
        fetch(statusUrl + id + '/status', { method: 'POST', body: body, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (j) {
                paintRow(row, j.status, j.rejection_reason); changed = true;
                document.dispatchEvent(new CustomEvent('hdv:doc-status', { detail: { id: id, status: j.status } }));
                var opened = el('hdvViewer').classList.contains('open');
                if (opened && fromViewer) {
                    var n = nextPending();
                    if (n >= 0) { idx = n; render(); showReject(false); }
                    else { render(); showReject(false); }
                } else if (opened) { render(); }
            })
            .catch(function () { alert('No se pudo actualizar el documento. Intenta de nuevo.'); });
    }
    function confirmReject() {
        var reason = el('hvReason').value.trim();
        if (!reason) { el('hvReason').focus(); el('hvReason').style.borderColor = '#dc2626'; return; }
        setStatus(current().dataset.docId, 'rejected', true, reason);
    }

    document.addEventListener('click', function (e) {
        if (e.target.classList && e.target.classList.contains('hv-chip')) { el('hvReason').value = e.target.dataset.reason; }
    });
    document.addEventListener('keydown', function (e) {
        if (!el('hdvViewer').classList.contains('open')) return;
        var typing = /TEXTAREA|INPUT/.test((e.target.tagName || ''));
        if (e.key === 'Escape') close();
        else if (typing) return;
        else if (e.key === 'ArrowRight') step(1);
        else if (e.key === 'ArrowLeft') step(-1);
        else if (e.key === 'a' || e.key === 'A') { var c = current(); if (c && c.dataset.status !== 'verified') setStatus(c.dataset.docId, 'verified', true); }
        else if (e.key === 'r' || e.key === 'R') { e.preventDefault(); showReject(true); }
    });

    return { open: open, close: close, step: step, rotate: rotate, toggleZoom: toggleZoom, setStatus: setStatus,
             showReject: showReject, confirmReject: confirmReject, openFirstPending: openFirstPending,
             currentId: function () { return current() ? current().dataset.docId : null; } };
})();
</script>
