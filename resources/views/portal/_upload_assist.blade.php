{{-- Asistente de subida del Portal (2026-09-25): vista previa "¿se lee bien?", rotar, varias hojas → un PDF
     y compresión de fotos pesadas, ANTES de enviar. Se activa en los <input type=file data-hdv-assist>.
     Sin dependencias: el PDF se arma aquí mismo con las fotos JPEG. Ver docs/funcionalidades/documentos-y-revision.md --}}
<style>
#hdvAssist { position:fixed; inset:0; z-index:10000; background:rgba(15,23,42,.72); display:none; align-items:center; justify-content:center; padding:1rem; }
#hdvAssist.open { display:flex; }
.ha-card { background:#fff; border-radius:16px; width:min(640px,100%); max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 70px rgba(0,0,0,.35); }
.ha-head { padding:1rem 1.25rem .5rem; }
.ha-head h3 { margin:0; font-size:1.05rem; color:#0f172a; }
.ha-head p { margin:.25rem 0 0; font-size:.8rem; color:#64748b; }
.ha-body { padding:.5rem 1.25rem; overflow-y:auto; }
.ha-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:.75rem; }
.ha-page { border:1px solid #e2e8f0; border-radius:10px; padding:.5rem; background:#f8fafc; text-align:center; }
.ha-thumb { height:150px; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:6px; background:#fff; }
.ha-thumb img { max-width:100%; max-height:100%; transition:transform .15s; }
.ha-tools { display:flex; justify-content:center; gap:.35rem; margin-top:.4rem; }
.ha-tools button { border:1px solid #cbd5e1; background:#fff; border-radius:6px; padding:.2rem .5rem; font-size:.75rem; cursor:pointer; }
.ha-warn { font-size:.7rem; color:#b45309; margin-top:.3rem; }
.ha-add { border:2px dashed #cbd5e1; border-radius:10px; display:flex; align-items:center; justify-content:center; min-height:150px; color:#1D4ED8; font-weight:600; font-size:.8rem; cursor:pointer; background:#fff; }
.ha-check { margin:.75rem 0 .25rem; background:#eff6ff; border-radius:10px; padding:.7rem .9rem; font-size:.78rem; color:#1e3a8a; line-height:1.5; }
.ha-foot { padding:.85rem 1.25rem 1.1rem; display:flex; gap:.6rem; justify-content:flex-end; flex-wrap:wrap; border-top:1px solid #e2e8f0; }
.ha-btn { border:0; border-radius:10px; padding:.65rem 1.1rem; font-size:.88rem; font-weight:700; cursor:pointer; }
.ha-btn.primary { background:#1D4ED8; color:#fff; }
.ha-btn.ghost { background:#f1f5f9; color:#334155; }
.ha-btn[disabled] { opacity:.6; cursor:wait; }
</style>

<div id="hdvAssist" role="dialog" aria-modal="true" aria-labelledby="haTitle">
    <div class="ha-card">
        <div class="ha-head">
            <h3 id="haTitle">Revisa tu documento antes de enviarlo</h3>
            <p id="haSub"></p>
        </div>
        <div class="ha-body">
            <div class="ha-grid" id="haGrid"></div>
            <div class="ha-check">
                <strong>¿Se lee bien?</strong> Comprueba que:<br>
                ✅ se ve completo, con los cuatro bordes<br>
                ✅ puedes leer nombres, fechas y montos sin acercarte<br>
                ✅ no hay reflejos, sombras ni dedos encima<br>
                <span id="haTip" style="display:block;margin-top:.35rem;color:#475569;"></span>
            </div>
        </div>
        <div class="ha-foot">
            <button type="button" class="ha-btn ghost" id="haCancel">Volver a elegir</button>
            <button type="button" class="ha-btn primary" id="haSend">Se lee bien, enviar</button>
        </div>
    </div>
</div>
<input type="file" id="haMore" accept="image/*" multiple style="display:none;">

<script>
window.hdvUploadAssist = (function () {
    var MAX_SIDE = 2000, QUALITY = 0.86, MIN_SHORT = 600;
    var pages = [], targetInput = null, busy = false;
    var $ = function (id) { return document.getElementById(id); };

    function isImage(f) { return /^image\/(jpeg|jpg|png)$/i.test(f.type) || /\.(jpe?g|png)$/i.test(f.name || ''); }

    // Carga y reduce (lados > MAX_SIDE) para que las fotos de celular de varios MB no rebasen el límite de 10 MB.
    function loadPage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file), img = new Image();
            img.onload = function () {
                var w = img.naturalWidth, h = img.naturalHeight, s = Math.min(1, MAX_SIDE / Math.max(w, h));
                var c = document.createElement('canvas'); c.width = Math.round(w * s); c.height = Math.round(h * s);
                var ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); ctx.drawImage(img, 0, 0, c.width, c.height);
                URL.revokeObjectURL(url);
                resolve({ canvas: c, origW: w, origH: h, rot: 0, name: file.name || 'documento.jpg', preview: c.toDataURL('image/jpeg', 0.6) });
            };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('decode')); };
            img.src = url;
        });
    }

    function rotatedCanvas(p) {
        if (!p.rot) return p.canvas;
        var c = document.createElement('canvas'), swap = p.rot % 180 !== 0;
        c.width = swap ? p.canvas.height : p.canvas.width; c.height = swap ? p.canvas.width : p.canvas.height;
        var ctx = c.getContext('2d'); ctx.translate(c.width / 2, c.height / 2); ctx.rotate(p.rot * Math.PI / 180);
        ctx.drawImage(p.canvas, -p.canvas.width / 2, -p.canvas.height / 2);
        return c;
    }

    function toBlob(canvas) { return new Promise(function (res) { canvas.toBlob(res, 'image/jpeg', QUALITY); }); }

    // ── PDF mínimo: una página por foto (JPEG embebido con DCTDecode), sin librerías ──
    function buildPdf(items) { // items: [{bytes:Uint8Array, w, h}]
        var enc = new TextEncoder(), chunks = [], offset = 0, offsets = {};
        function push(x) { var b = typeof x === 'string' ? enc.encode(x) : x; chunks.push(b); offset += b.length; }
        function begin(n) { offsets[n] = offset; push(n + ' 0 obj\n'); }
        push('%PDF-1.4\n%\xE2\xE3\xCF\xD3\n');
        var kids = items.map(function (_, i) { return (3 + i * 3) + ' 0 R'; }).join(' ');
        begin(1); push('<< /Type /Catalog /Pages 2 0 R >>\nendobj\n');
        begin(2); push('<< /Type /Pages /Kids [' + kids + '] /Count ' + items.length + ' >>\nendobj\n');
        items.forEach(function (it, i) {
            var pid = 3 + i * 3, cid = pid + 1, iid = pid + 2;
            var landscape = it.w > it.h, pw = landscape ? 842 : 595, ph = landscape ? 595 : 842;
            var s = Math.min(pw / it.w, ph / it.h), dw = it.w * s, dh = it.h * s, x = (pw - dw) / 2, y = (ph - dh) / 2;
            var content = 'q ' + dw.toFixed(2) + ' 0 0 ' + dh.toFixed(2) + ' ' + x.toFixed(2) + ' ' + y.toFixed(2) + ' cm /Im0 Do Q';
            begin(pid); push('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' + pw + ' ' + ph + '] /Resources << /XObject << /Im0 ' + iid + ' 0 R >> >> /Contents ' + cid + ' 0 R >>\nendobj\n');
            begin(cid); push('<< /Length ' + content.length + ' >>\nstream\n' + content + '\nendstream\nendobj\n');
            begin(iid); push('<< /Type /XObject /Subtype /Image /Width ' + it.w + ' /Height ' + it.h + ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' + it.bytes.length + ' >>\nstream\n');
            push(it.bytes); push('\nendstream\nendobj\n');
        });
        var total = 2 + items.length * 3, xrefAt = offset;
        push('xref\n0 ' + (total + 1) + '\n0000000000 65535 f \n');
        for (var n = 1; n <= total; n++) push(('0000000000' + offsets[n]).slice(-10) + ' 00000 n \n');
        push('trailer\n<< /Size ' + (total + 1) + ' /Root 1 0 R >>\nstartxref\n' + xrefAt + '\n%%EOF');
        return new Blob(chunks, { type: 'application/pdf' });
    }

    function render() {
        var grid = $('haGrid'); grid.innerHTML = '';
        pages.forEach(function (p, i) {
            var d = document.createElement('div'); d.className = 'ha-page';
            var small = Math.min(p.origW, p.origH) < MIN_SHORT;
            d.innerHTML = '<div class="ha-thumb"><img alt="Hoja ' + (i + 1) + '" style="transform:rotate(' + p.rot + 'deg)"></div>' +
                '<div style="font-size:.72rem;color:#64748b;margin-top:.3rem;">Hoja ' + (i + 1) + '</div>' +
                '<div class="ha-tools"><button type="button" data-a="rot" data-i="' + i + '" title="Girar">⟳ Girar</button><button type="button" data-a="del" data-i="' + i + '" title="Quitar">✕</button></div>' +
                (small ? '<div class="ha-warn">⚠ Se ve muy pequeña (' + p.origW + '×' + p.origH + '). Tómala de nuevo con la cámara en su tamaño original.</div>' : '');
            d.querySelector('img').src = p.preview; grid.appendChild(d);
        });
        var add = document.createElement('label'); add.className = 'ha-add'; add.setAttribute('for', 'haMore'); add.innerHTML = '➕ Agregar otra hoja'; grid.appendChild(add);
        $('haSub').textContent = pages.length > 1 ? pages.length + ' hojas — se enviarán juntas en un solo PDF.' : 'Si el documento tiene más hojas, agrégalas y se enviarán juntas en un solo PDF.';
        $('haSend').textContent = pages.length > 1 ? 'Se leen bien, enviar PDF' : 'Se lee bien, enviar';
    }

    function close() {
        $('hdvAssist').classList.remove('open'); document.body.style.overflow = '';
        if (targetInput) { try { targetInput.value = ''; } catch (e) {} }
        pages = []; targetInput = null; busy = false; $('haSend').disabled = false;
    }

    function passthrough(input, files) { deliver(input, files); }

    // Entrega el/los archivos finales al input y deja que Livewire los suba (evento marcado para no volver a interceptarlo).
    function deliver(input, files) {
        var dt = new DataTransfer(); files.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
        ['input', 'change'].forEach(function (t) { var ev = new Event(t, { bubbles: true }); ev.__hdv = true; input.dispatchEvent(ev); });
    }

    function openWith(input, files) {
        targetInput = input; busy = false;
        $('haTip').textContent = input.getAttribute('data-hdv-tip') || '';
        Promise.all(files.map(loadPage)).then(function (list) {
            pages = list; render();
            $('hdvAssist').classList.add('open'); document.body.style.overflow = 'hidden';
        }).catch(function () {
            // Formato que el navegador no decodifica (p. ej. HEIC): se envía tal cual, sin asistente.
            var t = input; targetInput = null; passthrough(t, files);
        });
    }

    function send() {
        if (busy || !pages.length) return; busy = true; $('haSend').disabled = true; $('haSend').textContent = 'Preparando…';
        var input = targetInput, base = (pages[0].name || 'documento').replace(/\.[^.]+$/, '');
        Promise.all(pages.map(function (p) { return toBlob(rotatedCanvas(p)); })).then(function (blobs) {
            if (blobs.length === 1) return [new File([blobs[0]], base + '.jpg', { type: 'image/jpeg' })];
            return Promise.all(blobs.map(function (b, i) {
                var c = rotatedCanvas(pages[i]);
                return b.arrayBuffer().then(function (buf) { return { bytes: new Uint8Array(buf), w: c.width, h: c.height }; });
            })).then(function (items) { return [new File([buildPdf(items)], base + '.pdf', { type: 'application/pdf' })]; });
        }).then(function (files) {
            targetInput = null; $('hdvAssist').classList.remove('open'); document.body.style.overflow = ''; pages = []; busy = false;
            deliver(input, files);
        }).catch(function () { busy = false; $('haSend').disabled = false; alert('No pudimos preparar el documento. Intenta de nuevo.'); });
    }

    // Se intercepta la selección REAL del usuario (isTrusted); la cámara guiada y nuestro reenvío pasan directo.
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input || input.type !== 'file' || !input.hasAttribute('data-hdv-assist')) return;
        if (!e.isTrusted || e.__hdv) return;
        var files = Array.prototype.slice.call(input.files || []);
        if (!files.length || !files.every(isImage)) return; // PDF/doc: sin asistente
        e.stopImmediatePropagation();
        openWith(input, files);
    }, true);

    document.addEventListener('click', function (e) {
        var b = e.target.closest && e.target.closest('#haGrid button');
        if (!b) return;
        var i = parseInt(b.getAttribute('data-i'), 10);
        if (b.getAttribute('data-a') === 'rot') { pages[i].rot = (pages[i].rot + 90) % 360; render(); }
        else if (b.getAttribute('data-a') === 'del') { pages.splice(i, 1); if (!pages.length) close(); else render(); }
    });
    $('haCancel').addEventListener('click', close);
    $('haSend').addEventListener('click', send);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && $('hdvAssist').classList.contains('open')) close(); });
    $('haMore').addEventListener('change', function () {
        var files = Array.prototype.slice.call(this.files || []).filter(isImage); this.value = '';
        Promise.all(files.map(loadPage)).then(function (list) { pages = pages.concat(list); render(); }).catch(function () { alert('No pudimos leer esa imagen.'); });
    });

    // open: gancho para pruebas (un change sintético no es 'trusted' y no pasaría por el interceptor)
    return { buildPdf: buildPdf, open: openWith, _pages: function () { return pages; } };
})();
</script>
