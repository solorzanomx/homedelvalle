{{-- JS de la cámara guiada para identificaciones (INE frente/reverso, pasaporte). Sin <script>: se incluye DENTRO de uno.
     Lo usan portal/expediente y portal/documents/tenant; los elementos del modal viven en livewire/portal/document-uploader. --}}
// ═══════════════════════════════════════════════════════════════
// Cámara guiada para identificación (INE frente/reverso, pasaporte)
// Los ids de los elementos del modal viven en el componente Livewire
// portal.document-uploader y usan la categoría del documento (única por
// instancia) para poder abrirse entre sí sin acoplarse al id interno de
// Livewire — ver resources/views/livewire/portal/document-uploader.blade.php
// ═══════════════════════════════════════════════════════════════
var idCamStreams = {};

function idCamOpen(category) {
    var modal = document.getElementById('cam-modal-' + category);
    if (!modal) return;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    idCamShowLive(category);

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        idCamShowError(category, 'Tu navegador no permite usar la cámara aquí. Cierra esto y usa "Subir archivo".');
        return;
    }

    navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
        audio: false
    }).then(function (stream) {
        idCamStreams[category] = stream;
        var video = document.getElementById('cam-video-' + category);
        if (video) video.srcObject = stream;
    }).catch(function (err) {
        idCamShowError(category, 'No se pudo abrir la cámara (permiso denegado o no disponible). Cierra esto y usa "Subir archivo".');
    });
}

function idCamShowLive(category) {
    var video = document.getElementById('cam-video-' + category);
    var preview = document.getElementById('cam-preview-' + category);
    var live = document.getElementById('cam-controls-live-' + category);
    var prev = document.getElementById('cam-controls-preview-' + category);
    var err = document.getElementById('cam-error-' + category);
    if (video) video.style.display = '';
    if (preview) preview.style.display = 'none';
    if (live) live.style.display = 'flex';
    if (prev) prev.style.display = 'none';
    if (err) err.hidden = true;
}

function idCamShowError(category, msg) {
    var err = document.getElementById('cam-error-' + category);
    if (err) { err.hidden = false; err.textContent = msg; }
}

// Mapea el recuadro guía (en pantalla) a coordenadas de pixel reales del
// video, tomando en cuenta que el <video> usa object-fit:cover (se recorta
// simétricamente para llenar el contenedor manteniendo proporción).
function idCamGuideToVideoRect(video, guideBox) {
    var containerRect = video.getBoundingClientRect();
    var guideRect = guideBox.getBoundingClientRect();

    var vw = video.videoWidth, vh = video.videoHeight;
    var cw = containerRect.width, ch = containerRect.height;
    var scale = Math.max(cw / vw, ch / vh);
    var renderedW = vw * scale, renderedH = vh * scale;
    var offsetX = (cw - renderedW) / 2;
    var offsetY = (ch - renderedH) / 2;

    var gx = guideRect.left - containerRect.left;
    var gy = guideRect.top - containerRect.top;

    var sx = (gx - offsetX) / scale;
    var sy = (gy - offsetY) / scale;
    var sw = guideRect.width / scale;
    var sh = guideRect.height / scale;

    sx = Math.max(0, Math.min(sx, vw));
    sy = Math.max(0, Math.min(sy, vh));
    sw = Math.min(sw, vw - sx);
    sh = Math.min(sh, vh - sy);

    return { sx: sx, sy: sy, sw: sw, sh: sh };
}

function idCamCapture(category) {
    var video   = document.getElementById('cam-video-' + category);
    var canvas  = document.getElementById('cam-canvas-' + category);
    var preview = document.getElementById('cam-preview-' + category);
    var guideBox = document.getElementById('cam-guidebox-' + category);
    if (!video || !canvas || !preview || !guideBox || !video.videoWidth) return;

    // Recorta EXACTAMENTE lo que se ve dentro del recuadro guía — la foto
    // que se sube es la credencial sola, no la pantalla completa con espacio
    // alrededor (eso era lo que hacía que la IA leyera mal los datos).
    var crop = idCamGuideToVideoRect(video, guideBox);
    // La proporción de salida sale del recuadro real en pantalla (credencial
    // vs. página de pasaporte usan proporciones distintas), no un valor fijo.
    var guideRatio = guideBox.offsetWidth / guideBox.offsetHeight;
    var outW = 1013, outH = Math.round(outW / guideRatio);
    canvas.width = outW;
    canvas.height = outH;
    canvas.getContext('2d').drawImage(video, crop.sx, crop.sy, crop.sw, crop.sh, 0, 0, outW, outH);

    preview.src = canvas.toDataURL('image/jpeg', 0.92);
    video.style.display = 'none';
    preview.style.display = '';
    document.getElementById('cam-controls-live-' + category).style.display = 'none';
    document.getElementById('cam-controls-preview-' + category).style.display = 'flex';
}

function idCamRetake(category) {
    idCamShowLive(category);
}

function idCamConfirm(category) {
    var canvas = document.getElementById('cam-canvas-' + category);
    if (!canvas) return;
    canvas.toBlob(function (blob) {
        if (!blob) return;
        var file  = new File([blob], category + '.jpg', { type: 'image/jpeg' });
        var input = document.getElementById('slot-input-' + category);
        if (input) {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        idCamClose(category);
        document.dispatchEvent(new CustomEvent('id-photo-captured', { detail: { category: category } }));
    }, 'image/jpeg', 0.92);
}

function idCamClose(category) {
    var modal = document.getElementById('cam-modal-' + category);
    if (modal) modal.hidden = true;
    document.body.style.overflow = '';
    var stream = idCamStreams[category];
    if (stream) {
        stream.getTracks().forEach(function (t) { t.stop(); });
        delete idCamStreams[category];
    }
}

// Frente → Reverso: al confirmar el frente, ofrece de una vez la vuelta.
document.addEventListener('id-photo-captured', function (e) {
    var nextMap = { 'ine_frente': 'ine_reverso', 'aval_ine_frente': 'aval_ine_reverso' };
    var next = nextMap[e.detail.category];
    if (next && document.getElementById('cam-modal-' + next)) {
        setTimeout(function () {
            if (confirm('Frente capturado. ¿Tomar la foto de la vuelta ahora?')) {
                idCamOpen(next);
            }
        }, 350);
    }
});

