@extends('layouts.app-sidebar')
@section('title', 'Nuevo Template')

@section('styles')
<style>
    .editor-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:0; }
    .editor-tab {
        padding:0.55rem 1.2rem; font-size:0.82rem; font-weight:500; cursor:pointer;
        border:none; background:none; color:var(--text-muted); border-bottom:2px solid transparent;
        margin-bottom:-2px; transition:all 0.15s; font-family:inherit;
    }
    .editor-tab:hover { color:var(--text); }
    .editor-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
    .editor-panel { display:none; }
    .editor-panel.active { display:block; }
    .preview-frame {
        min-height:350px; border:1px solid var(--border); border-radius:var(--radius);
        background:#fff; padding:1rem;
    }
    .var-chip {
        display:inline-flex; align-items:center; gap:0.3rem; padding:0.2rem 0.6rem; font-size:0.75rem;
        background:var(--bg); border:1px solid var(--border); border-radius:4px; cursor:pointer;
        transition:all 0.15s;
    }
    .var-chip:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
    .var-chip .var-key { font-family:monospace; font-size:0.68rem; opacity:0.7; }
    .tox-tinymce { border-radius:var(--radius) !important; border-color:var(--border) !important; }

    /* Gallery Modal */
    .modal-overlay {
        display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000;
        align-items:center; justify-content:center;
    }
    .modal-overlay.active { display:flex; }
    .modal-box {
        background:var(--card); border-radius:10px; width:90%; max-width:720px; max-height:80vh;
        display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,0.2);
    }
    .modal-header {
        padding:1rem 1.25rem; border-bottom:1px solid var(--border);
        display:flex; align-items:center; justify-content:space-between;
    }
    .modal-header h3 { font-size:0.95rem; font-weight:600; }
    .modal-close { background:none; border:none; font-size:1.3rem; cursor:pointer; color:var(--text-muted); padding:0.25rem; }
    .modal-body { padding:1.25rem; overflow-y:auto; flex:1; }
    .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:0.75rem; }
    .gallery-item {
        border:2px solid var(--border); border-radius:var(--radius); overflow:hidden; cursor:pointer;
        transition:all 0.15s;
    }
    .gallery-item:hover { border-color:var(--primary); box-shadow:0 2px 8px rgba(102,126,234,0.2); }
    .gallery-item img { width:100%; height:90px; object-fit:cover; display:block; }
    .gallery-item-name { padding:0.35rem 0.5rem; font-size:0.72rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .gallery-empty { text-align:center; padding:2rem; color:var(--text-muted); }

    /* Upload tab inside modal */
    .modal-tabs { display:flex; gap:0; border-bottom:1px solid var(--border); margin-bottom:1rem; }
    .modal-tab {
        padding:0.45rem 1rem; font-size:0.8rem; font-weight:500; cursor:pointer;
        border:none; background:none; color:var(--text-muted); border-bottom:2px solid transparent;
        margin-bottom:-1px; font-family:inherit;
    }
    .modal-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
    .modal-panel { display:none; }
    .modal-panel.active { display:block; }
    .upload-drop {
        border:2px dashed var(--border); border-radius:var(--radius); padding:1.5rem; text-align:center;
        cursor:pointer; transition:all 0.15s;
    }
    .upload-drop:hover { border-color:var(--primary); background:rgba(102,126,234,0.03); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Crear Template de Email</h2></div>
    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline">Volver</a>
</div>

<form method="POST" action="{{ route('admin.email.templates.store') }}" id="templateForm">
    @csrf
    <div style="display:grid; grid-template-columns:1fr 280px; gap:1.5rem; align-items:start;">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre del Template <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" value="{{ old('name') }}" required
                                   placeholder="Ej: BienvenidaUsuario">
                            @error('name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Asunto <span class="required">*</span></label>
                            <input type="text" name="subject" class="form-input" value="{{ old('subject') }}" required
                                   placeholder="Ej: Bienvenido a tu cuenta">
                            @error('subject') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="padding:0; border:none;">
                    <div class="editor-tabs" style="width:100%; padding:0 1.5rem;">
                        <button type="button" class="editor-tab active" data-tab="visual">Visual</button>
                        <button type="button" class="editor-tab" data-tab="html">Codigo HTML</button>
                        <button type="button" class="editor-tab" data-tab="text">Texto Plano</button>
                        <button type="button" class="editor-tab" data-tab="preview">Vista Previa</button>
                    </div>
                </div>
                <div class="card-body" style="padding-top:0.75rem;">
                    {{-- Visual WYSIWYG --}}
                    <div class="editor-panel active" id="panel-visual">
                        <textarea id="wysiwygEditor">{{ old('body') }}</textarea>
                    </div>

                    {{-- HTML Code --}}
                    <div class="editor-panel" id="panel-html">
                        <textarea name="body" id="bodyHtml" class="form-textarea" required
                                  style="font-family:monospace; font-size:0.82rem; min-height:400px;">{{ old('body') }}</textarea>
                        @error('body') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>

                    {{-- Text Editor --}}
                    <div class="editor-panel" id="panel-text">
                        <p class="form-hint" style="margin-bottom:0.5rem;">Version en texto plano del correo. Si se deja vacio, se generara automaticamente del HTML.</p>
                        <textarea name="body_text" id="bodyText" class="form-textarea"
                                  style="font-family:monospace; font-size:0.82rem; min-height:350px;">{{ old('body_text') }}</textarea>
                    </div>

                    {{-- Preview --}}
                    <div class="editor-panel" id="panel-preview">
                        <div class="preview-frame" id="previewFrame"></div>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="border:none; padding-top:0;">
                <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Template</button>
            </div>
        </div>

        {{-- Sidebar derecho --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Insertar Variable</h3></div>
                <div class="card-body">
                    <p class="form-hint" style="margin-bottom:0.75rem;">Clic para insertar en el editor:</p>
                    <div style="display:flex; flex-wrap:wrap; gap:0.4rem;" id="variableChips">
                        {{-- Variable chips with human-readable labels --}}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Galeria de Imagenes</h3>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline" style="width:100%; justify-content:center;" onclick="openGalleryModal()">
                        &#128444; Abrir Galeria
                    </button>
                    <p class="form-hint" style="margin-top:0.5rem;">Selecciona imagenes de la galeria para insertarlas en el template.</p>
                    <div style="margin-top:0.75rem;">
                        <a href="{{ route('admin.email.assets.index') }}" class="btn btn-sm btn-outline" style="width:100%; justify-content:center;">Administrar Assets</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>&#9993; Enviar Prueba</h3></div>
                <div class="card-body">
                    <p class="form-hint" style="margin-bottom:0.5rem;">Envia el template actual a un correo para verificar como se ve.</p>
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <input type="email" id="testEmail" class="form-input" placeholder="correo@ejemplo.com">
                    </div>
                    <button type="button" class="btn btn-primary" style="width:100%; justify-content:center;" onclick="sendTestEmail()" id="btnSendTest">
                        Enviar Correo de Prueba
                    </button>
                    <div id="testResult" style="margin-top:0.5rem;"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Ayuda</h3></div>
                <div class="card-body" style="font-size:0.82rem; color:var(--text-muted);">
                    <p style="margin-bottom:0.5rem;"><strong>Visual:</strong> Editor tipo Word con formato visual.</p>
                    <p style="margin-bottom:0.5rem;"><strong>HTML:</strong> Codigo HTML directo para control total.</p>
                    <p style="margin-bottom:0.5rem;"><strong>Texto Plano:</strong> Alternativa sin formato.</p>
                    <p><strong>Variables:</strong> Se reemplazan automaticamente al enviar el correo.</p>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Gallery Modal --}}
<div class="modal-overlay" id="galleryModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>&#128444; Galeria de Imagenes</h3>
            <button class="modal-close" onclick="closeGalleryModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-tabs">
                <button type="button" class="modal-tab active" onclick="switchModalTab('browse')">Explorar</button>
                <button type="button" class="modal-tab" onclick="switchModalTab('upload')">Subir Nueva</button>
            </div>
            <div class="modal-panel active" id="modalBrowse">
                <div class="gallery-grid" id="galleryGrid"></div>
                <div class="gallery-empty" id="galleryEmpty" style="display:none;">
                    <p>&#128444;</p><p>No hay imagenes. Sube una desde la pestana "Subir Nueva".</p>
                </div>
            </div>
            <div class="modal-panel" id="modalUpload">
                <div class="upload-drop" onclick="document.getElementById('modalFileInput').click()">
                    <input type="file" id="modalFileInput" accept="image/*" style="display:none" onchange="uploadAssetFromModal(this)">
                    <p style="font-size:1.5rem; margin-bottom:0.3rem;">&#128247;</p>
                    <p style="font-weight:500;">Haz clic para seleccionar imagen</p>
                    <p class="text-muted" style="font-size:0.78rem;">JPG, PNG, GIF, WebP, SVG (max 5MB)</p>
                </div>
                <div id="modalUploadStatus" style="margin-top:0.75rem;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
var currentTab = 'visual';
var tinyEditor = null;

// Variable definitions with human-readable labels
var templateVariables = [
    { key: 'Nombre', label: 'Nombre del usuario' },
    { key: 'Apellido', label: 'Apellido del usuario' },
    { key: 'Email', label: 'Correo electronico' },
    { key: 'Password', label: 'Contrasena asignada' },
    { key: 'Fecha', label: 'Fecha actual' },
    { key: 'Rol', label: 'Rol del usuario' },
    { key: 'Sitio', label: 'Nombre del sitio' }
];

// Build variable chips
(function() {
    var container = document.getElementById('variableChips');
    templateVariables.forEach(function(v) {
        var chip = document.createElement('span');
        chip.className = 'var-chip';
        chip.onclick = function() { insertVariable(v.key); };
        chip.innerHTML = v.label + ' <span class="var-key">{{' + v.key + '}}</span>';
        container.appendChild(chip);
    });
})();

// Init TinyMCE
tinymce.init({
    selector: '#wysiwygEditor',
    height: 420,
    menubar: 'edit insert format table',
    plugins: 'lists link image table code fullscreen preview',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; padding: 8px; }',
    branding: false,
    license_key: 'gpl',
    relative_urls: false,
    remove_script_host: false,
    setup: function(editor) {
        tinyEditor = editor;

        editor.ui.registry.addMenuButton('insertvariable', {
            text: 'Variables',
            fetch: function(callback) {
                var items = templateVariables.map(function(v) {
                    return {
                        type: 'menuitem',
                        text: v.label + ' ({{' + v.key + '}})',
                        onAction: function() {
                            editor.insertContent('{{' + v.key + '}}');
                        }
                    };
                });
                callback(items);
            }
        });

        editor.ui.registry.addButton('imagegallery', {
            text: 'Galeria',
            icon: 'image',
            onAction: function() {
                openGalleryModal();
            }
        });
    },
    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image imagegallery | insertvariable | table | code fullscreen',
    images_upload_handler: function(blobInfo) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            fetch('{{ route("admin.email.templates.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) resolve(data.url);
                else reject('Error al subir imagen');
            })
            .catch(function() { reject('Error al subir imagen'); });
        });
    }
});

// Tab switching
document.querySelectorAll('.editor-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        var targetTab = this.getAttribute('data-tab');
        switchTab(targetTab);
    });
});

function switchTab(tab) {
    // Sync content between editors before switching
    syncEditors(currentTab, tab);

    document.querySelectorAll('.editor-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.editor-panel').forEach(function(p) { p.classList.remove('active'); });
    document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
    currentTab = tab;

    if (tab === 'preview') {
        document.getElementById('previewFrame').innerHTML = document.getElementById('bodyHtml').value;
    }
}

function syncEditors(from, to) {
    if (from === 'visual' && tinyEditor) {
        document.getElementById('bodyHtml').value = tinyEditor.getContent();
    } else if (from === 'html' && to === 'visual' && tinyEditor) {
        tinyEditor.setContent(document.getElementById('bodyHtml').value);
    }
    // When going from visual, always update the hidden field
    if (from === 'visual' && tinyEditor) {
        document.getElementById('bodyHtml').value = tinyEditor.getContent();
    }
}

// Insert variable into current active editor
function insertVariable(key) {
    var varText = '{{' + key + '}}';
    if (currentTab === 'visual' && tinyEditor) {
        tinyEditor.insertContent(varText);
    } else if (currentTab === 'html') {
        insertAtCursor(document.getElementById('bodyHtml'), varText);
    } else if (currentTab === 'text') {
        insertAtCursor(document.getElementById('bodyText'), varText);
    }
}

function insertAtCursor(textarea, text) {
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + text.length;
    textarea.focus();
}

// On form submit, sync WYSIWYG to hidden textarea
document.getElementById('templateForm').addEventListener('submit', function() {
    if (tinyEditor) {
        document.getElementById('bodyHtml').value = tinyEditor.getContent();
    }
});

// ===== Gallery Modal =====
function openGalleryModal() {
    document.getElementById('galleryModal').classList.add('active');
    loadGalleryImages();
}
function closeGalleryModal() {
    document.getElementById('galleryModal').classList.remove('active');
}
// Close on overlay click
document.getElementById('galleryModal').addEventListener('click', function(e) {
    if (e.target === this) closeGalleryModal();
});

function switchModalTab(tab) {
    document.querySelectorAll('.modal-tab').forEach(function(t) { t.classList.remove('active'); });
    document.querySelectorAll('.modal-panel').forEach(function(p) { p.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById(tab === 'browse' ? 'modalBrowse' : 'modalUpload').classList.add('active');
}

function loadGalleryImages() {
    var grid = document.getElementById('galleryGrid');
    var empty = document.getElementById('galleryEmpty');
    grid.innerHTML = '<p class="text-muted">Cargando...</p>';

    fetch('{{ route("admin.email.assets.gallery") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(assets) {
        grid.innerHTML = '';
        if (assets.length === 0) {
            empty.style.display = 'block';
            return;
        }
        empty.style.display = 'none';
        assets.forEach(function(asset) {
            var item = document.createElement('div');
            item.className = 'gallery-item';
            item.innerHTML = '<img src="' + asset.url + '" alt="' + asset.name + '">'
                + '<div class="gallery-item-name">' + asset.name + '</div>';
            item.onclick = function() { insertGalleryImage(asset.url, asset.name); };
            grid.appendChild(item);
        });
    })
    .catch(function() { grid.innerHTML = '<p class="text-muted">Error al cargar galeria</p>'; });
}

function insertGalleryImage(url, name) {
    var fullUrl = window.location.origin + url;
    var imgTag = '<img src="' + fullUrl + '" alt="' + name + '" style="max-width:100%;height:auto;">';
    if (currentTab === 'visual' && tinyEditor) {
        tinyEditor.insertContent(imgTag);
    } else if (currentTab === 'html') {
        insertAtCursor(document.getElementById('bodyHtml'), imgTag);
    }
    closeGalleryModal();
}

function uploadAssetFromModal(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { alert('Maximo 5MB'); input.value = ''; return; }

    var status = document.getElementById('modalUploadStatus');
    status.innerHTML = '<p class="text-muted">Subiendo...</p>';

    var formData = new FormData();
    formData.append('image', file);
    formData.append('name', file.name.replace(/\.[^/.]+$/, ''));
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('{{ route("admin.email.assets.store") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            status.innerHTML = '<div class="alert alert-success" style="margin:0;">Imagen subida. Puedes seleccionarla en la pestana Explorar.</div>';
            input.value = '';
            loadGalleryImages();
        }
    })
    .catch(function() { status.innerHTML = '<div class="alert alert-error" style="margin:0;">Error al subir imagen</div>'; });
}

// ===== Send Test Email =====
function sendTestEmail() {
    var email = document.getElementById('testEmail').value.trim();
    if (!email) { alert('Ingresa un correo de destino'); return; }

    // Sync WYSIWYG first
    if (tinyEditor) {
        document.getElementById('bodyHtml').value = tinyEditor.getContent();
    }

    var subject = document.querySelector('input[name="subject"]').value;
    var body = document.getElementById('bodyHtml').value;
    var bodyText = document.getElementById('bodyText').value;

    if (!subject || !body) { alert('Completa el asunto y cuerpo del template antes de enviar'); return; }

    var btn = document.getElementById('btnSendTest');
    var result = document.getElementById('testResult');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    result.innerHTML = '';

    var formData = new FormData();
    formData.append('test_email', email);
    formData.append('subject', subject);
    formData.append('body', body);
    formData.append('body_text', bodyText);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('{{ route("admin.email.templates.send-test") }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.textContent = 'Enviar Correo de Prueba';
        if (data.success) {
            result.innerHTML = '<div class="alert alert-success" style="margin:0; padding:0.5rem 0.8rem; font-size:0.8rem;">' + data.message + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-error" style="margin:0; padding:0.5rem 0.8rem; font-size:0.8rem;">' + data.message + '</div>';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.textContent = 'Enviar Correo de Prueba';
        result.innerHTML = '<div class="alert alert-error" style="margin:0; padding:0.5rem 0.8rem; font-size:0.8rem;">Error de conexion</div>';
    });
}
</script>
@endsection
