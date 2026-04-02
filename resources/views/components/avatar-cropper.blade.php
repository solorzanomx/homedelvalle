{{-- Avatar Cropper Modal Component --}}
{{-- Usage: <x-avatar-cropper :upload-url="route('profile.photo')" /> --}}
@props(['uploadUrl'])

<div id="cropperModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; max-width:460px; width:92%; padding:1.2rem; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.8rem;">
            <h3 style="margin:0; font-size:1rem; font-weight:600;">Ajustar foto de perfil</h3>
            <button onclick="closeCropper()" style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#666;">&times;</button>
        </div>
        <div style="width:100%; max-height:360px; overflow:hidden; border-radius:8px; background:#f0f0f0;">
            <img id="cropperImage" src="" style="display:block; max-width:100%;">
        </div>
        <div style="display:flex; align-items:center; gap:0.6rem; margin-top:0.8rem;">
            <button onclick="cropperInstance?.zoom(-0.1)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Alejar">&#8722;</button>
            <button onclick="cropperInstance?.zoom(0.1)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Acercar">&#43;</button>
            <button onclick="cropperInstance?.rotate(-90)" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:6px; padding:0.4rem 0.7rem; cursor:pointer; font-size:0.9rem;" title="Rotar">&#8635;</button>
            <div style="flex:1;"></div>
            <button onclick="closeCropper()" style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; padding:0.5rem 1rem; cursor:pointer; font-size:0.85rem;">Cancelar</button>
            <button onclick="applyCrop()" id="cropSaveBtn" style="background:var(--primary, #667eea); color:#fff; border:none; border-radius:8px; padding:0.5rem 1.2rem; cursor:pointer; font-size:0.85rem; font-weight:600;">Guardar</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
let cropperInstance = null;
const cropperUploadUrl = @json($uploadUrl);

function openCropper(file) {
    if (!file) return;
    const allowed = ['image/jpeg','image/png','image/jpg','image/gif','image/webp'];
    if (!allowed.includes(file.type)) { alert('Solo imagenes JPEG, PNG, GIF o WebP.'); return; }
    if (file.size > 5 * 1024 * 1024) { alert('Maximo 5MB.'); return; }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById('cropperImage');
        img.src = e.target.result;

        const modal = document.getElementById('cropperModal');
        modal.style.display = 'flex';

        if (cropperInstance) { cropperInstance.destroy(); }

        setTimeout(() => {
            cropperInstance = new Cropper(img, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                cropBoxResizable: false,
                cropBoxMovable: false,
                toggleDragModeOnDblclick: false,
                background: true,
                autoCropArea: 1,
                responsive: true,
                ready() {
                    // Round mask via CSS
                    const cropBox = this.cropper.querySelector('.cropper-view-box');
                    const face = this.cropper.querySelector('.cropper-face');
                    if (cropBox) cropBox.style.borderRadius = '50%';
                    if (face) face.style.borderRadius = '50%';
                }
            });
        }, 100);
    };
    reader.readAsDataURL(file);
}

function closeCropper() {
    document.getElementById('cropperModal').style.display = 'none';
    if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
}

function applyCrop() {
    if (!cropperInstance) return;
    const btn = document.getElementById('cropSaveBtn');
    btn.textContent = 'Guardando...';
    btn.disabled = true;

    const canvas = cropperInstance.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('avatar', blob, 'avatar.jpg');

        fetch(cropperUploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.avatar_url) {
                // Update all avatar images on the page
                document.querySelectorAll('[data-avatar-img]').forEach(el => {
                    el.src = data.avatar_url + '?t=' + Date.now();
                });
                // Replace placeholders with img
                document.querySelectorAll('[data-avatar-placeholder]').forEach(el => {
                    const img = document.createElement('img');
                    img.src = data.avatar_url + '?t=' + Date.now();
                    img.setAttribute('data-avatar-img', '');
                    img.alt = 'Avatar';
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:50%;';
                    el.parentNode.replaceChild(img, el);
                });
            }
            closeCropper();
            btn.textContent = 'Guardar';
            btn.disabled = false;
        })
        .catch(err => {
            alert('Error al subir la imagen');
            btn.textContent = 'Guardar';
            btn.disabled = false;
            console.error(err);
        });
    }, 'image/jpeg', 0.9);
}

// Close modal on backdrop click
document.getElementById('cropperModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCropper();
});
</script>
