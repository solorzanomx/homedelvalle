{{-- Media Browser Modal -- Include this partial wherever you need a media picker --}}
{{-- Usage: set window.mediaBrowserCallback = function(url, alt) { ... }; then call openMediaBrowser(); --}}
<div id="mediaBrowserModal" style="display: none; position: fixed; inset: 0; z-index: 2000; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: var(--card, #fff); border-radius: 12px; width: 800px; max-width: 95vw; max-height: 85vh; display: flex; flex-direction: column;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1rem;">Biblioteca de medios</h3>
            <button type="button" onclick="closeMediaBrowser()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted, #64748b);">&times;</button>
        </div>

        {{-- Tabs --}}
        <div style="display: flex; border-bottom: 1px solid var(--border, #e2e8f0);">
            <button type="button" class="mb-tab active" data-tab="browse" onclick="switchMediaTab('browse')" style="flex:1; padding: 0.6rem; font-size: 0.82rem; font-weight: 500; cursor: pointer; border: none; background: transparent; border-bottom: 2px solid var(--primary, #667eea); color: var(--primary, #667eea);">Explorar</button>
            <button type="button" class="mb-tab" data-tab="upload" onclick="switchMediaTab('upload')" style="flex:1; padding: 0.6rem; font-size: 0.82rem; font-weight: 500; cursor: pointer; border: none; background: transparent; border-bottom: 2px solid transparent; color: var(--text-muted, #64748b);">Subir nueva</button>
        </div>

        {{-- Browse tab --}}
        <div id="mbBrowseTab" style="flex: 1; overflow-y: auto; padding: 1rem 1.5rem;">
            <input type="text" id="mbSearch" placeholder="Buscar imagenes..." oninput="debounceMediaSearch()" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border, #e2e8f0); border-radius: 6px; font-size: 0.82rem; margin-bottom: 1rem;">
            <div id="mbGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem;"></div>
            <div id="mbLoadMore" style="text-align: center; padding: 1rem; display: none;">
                <button type="button" onclick="loadMoreMedia()" style="padding: 0.4rem 1rem; font-size: 0.78rem; border: 1px solid var(--border, #e2e8f0); border-radius: 6px; background: transparent; cursor: pointer;">Cargar mas</button>
            </div>
        </div>

        {{-- Upload tab --}}
        <div id="mbUploadTab" style="flex: 1; overflow-y: auto; padding: 1.5rem; display: none;">
            <div id="mbDropZone" style="border: 2px dashed var(--border, #e2e8f0); border-radius: 12px; padding: 3rem; text-align: center; cursor: pointer;" onclick="document.getElementById('mbFileInput').click()">
                <div style="font-size: 2rem; color: var(--text-muted, #64748b);">&#128228;</div>
                <p style="margin-top: 0.5rem; font-weight: 500;">Arrastra o haz click</p>
                <p style="font-size: 0.78rem; color: var(--text-muted, #64748b);">JPG, PNG, WebP, GIF. Max 10MB.</p>
                <input type="file" id="mbFileInput" multiple accept="image/*" style="display: none;">
            </div>
            <div id="mbUploadStatus" style="margin-top: 1rem; font-size: 0.82rem; color: var(--text-muted, #64748b);"></div>
        </div>

        {{-- Footer --}}
        <div id="mbFooter" style="padding: 0.75rem 1.5rem; border-top: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
            <span id="mbSelected" style="font-size: 0.82rem; color: var(--text-muted, #64748b);">Selecciona una imagen</span>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" onclick="closeMediaBrowser()" style="padding: 0.5rem 1rem; font-size: 0.82rem; border: 1px solid var(--border, #e2e8f0); border-radius: 6px; background: transparent; cursor: pointer;">Cancelar</button>
                <button type="button" id="mbInsertBtn" onclick="insertSelectedMedia()" disabled style="padding: 0.5rem 1rem; font-size: 0.82rem; border: none; border-radius: 6px; background: var(--primary, #667eea); color: #fff; cursor: pointer; opacity: 0.5;">Insertar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var mbData = { items: [], nextPage: null, selected: null, searchTimer: null };
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    window.openMediaBrowser = function() {
        document.getElementById('mediaBrowserModal').style.display = 'flex';
        mbData.selected = null;
        mbData.items = [];
        updateInsertBtn();
        switchMediaTab('browse');
        loadMediaItems('{{ route("admin.media.browse") }}');
    };

    window.closeMediaBrowser = function() {
        document.getElementById('mediaBrowserModal').style.display = 'none';
    };

    window.switchMediaTab = function(tab) {
        document.querySelectorAll('.mb-tab').forEach(function(btn) {
            var isActive = btn.dataset.tab === tab;
            btn.style.borderBottomColor = isActive ? 'var(--primary, #667eea)' : 'transparent';
            btn.style.color = isActive ? 'var(--primary, #667eea)' : 'var(--text-muted, #64748b)';
        });
        document.getElementById('mbBrowseTab').style.display = tab === 'browse' ? 'block' : 'none';
        document.getElementById('mbUploadTab').style.display = tab === 'upload' ? 'block' : 'none';
    };

    function loadMediaItems(url) {
        fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            renderMediaGrid(json.data);
            mbData.nextPage = json.next_page_url;
            document.getElementById('mbLoadMore').style.display = mbData.nextPage ? 'block' : 'none';
        });
    }

    window.loadMoreMedia = function() {
        if (mbData.nextPage) loadMediaItems(mbData.nextPage);
    };

    window.debounceMediaSearch = function() {
        clearTimeout(mbData.searchTimer);
        mbData.searchTimer = setTimeout(function() {
            var q = document.getElementById('mbSearch').value;
            mbData.items = [];
            loadMediaItems('{{ route("admin.media.browse") }}?search=' + encodeURIComponent(q));
        }, 300);
    };

    function renderMediaGrid(items) {
        var grid = document.getElementById('mbGrid');
        items.forEach(function(item) {
            mbData.items.push(item);
            var div = document.createElement('div');
            div.style.cssText = 'border-radius:8px;border:2px solid var(--border,#e2e8f0);overflow:hidden;cursor:pointer;transition:all 0.15s;';
            div.dataset.mediaId = item.id;
            div.innerHTML = '<div style="aspect-ratio:1;overflow:hidden;background:#f8fafc;"><img src="' + item.url + '" alt="' + (item.alt_text || '') + '" style="width:100%;height:100%;object-fit:cover;" loading="lazy"></div>';
            div.addEventListener('click', function() { selectMediaItem(item, div); });
            grid.appendChild(div);
        });
    }

    function selectMediaItem(item, el) {
        document.querySelectorAll('#mbGrid > div').forEach(function(d) {
            d.style.borderColor = 'var(--border, #e2e8f0)';
            d.style.boxShadow = 'none';
        });
        el.style.borderColor = 'var(--primary, #667eea)';
        el.style.boxShadow = '0 0 0 2px var(--primary, #667eea)';
        mbData.selected = item;
        updateInsertBtn();
    }

    function updateInsertBtn() {
        var btn = document.getElementById('mbInsertBtn');
        var label = document.getElementById('mbSelected');
        if (mbData.selected) {
            btn.disabled = false;
            btn.style.opacity = '1';
            label.textContent = mbData.selected.filename;
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            label.textContent = 'Selecciona una imagen';
        }
    }

    window.insertSelectedMedia = function() {
        if (mbData.selected && window.mediaBrowserCallback) {
            window.mediaBrowserCallback(mbData.selected.url, mbData.selected.alt_text || '');
        }
        closeMediaBrowser();
    };

    // Upload in modal
    var mbDropZone = document.getElementById('mbDropZone');
    if (mbDropZone) {
        ['dragenter', 'dragover'].forEach(function(ev) {
            mbDropZone.addEventListener(ev, function(e) { e.preventDefault(); mbDropZone.style.borderColor = 'var(--primary, #667eea)'; });
        });
        ['dragleave', 'drop'].forEach(function(ev) {
            mbDropZone.addEventListener(ev, function(e) { e.preventDefault(); mbDropZone.style.borderColor = 'var(--border, #e2e8f0)'; });
        });
        mbDropZone.addEventListener('drop', function(e) { mbUploadFiles(e.dataTransfer.files); });
        document.getElementById('mbFileInput').addEventListener('change', function() { mbUploadFiles(this.files); this.value = ''; });
    }

    function mbUploadFiles(files) {
        if (!files.length) return;
        var fd = new FormData();
        for (var i = 0; i < files.length; i++) fd.append('files[]', files[i]);
        document.getElementById('mbUploadStatus').textContent = 'Subiendo ' + files.length + ' archivo(s)...';

        fetch('{{ route("admin.media.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('mbUploadStatus').textContent = 'Subido! Cambiando a explorar...';
            setTimeout(function() {
                mbData.items = [];
                document.getElementById('mbGrid').innerHTML = '';
                switchMediaTab('browse');
                loadMediaItems('{{ route("admin.media.browse") }}');
                document.getElementById('mbUploadStatus').textContent = '';
            }, 500);
        })
        .catch(function() {
            document.getElementById('mbUploadStatus').textContent = 'Error al subir.';
        });
    }

    // Close on overlay click
    document.getElementById('mediaBrowserModal').addEventListener('click', function(e) {
        if (e.target === this) closeMediaBrowser();
    });
})();
</script>
