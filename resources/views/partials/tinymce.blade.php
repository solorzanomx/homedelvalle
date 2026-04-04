@props(['editorId' => 'wysiwygEditor', 'editorHeight' => 500, 'withImageUpload' => false])
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
tinymce.init({
    selector: '#{{ $editorId }}',
    height: {{ $editorHeight }},
    menubar: false,
    plugins: 'lists link table code fullscreen{{ $withImageUpload ? " image" : "" }}',
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link {{ $withImageUpload ? "image " : "" }}table | code fullscreen',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; padding: 8px; }',
    branding: false,
    license_key: 'gpl',
    relative_urls: false,
    @if($withImageUpload)
    images_upload_handler: function(blobInfo) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            fetch('{{ route("admin.cms.upload-image") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) { data.url ? resolve(data.url) : reject('Upload failed'); })
            .catch(function() { reject('Upload failed'); });
        });
    },
    @endif
    setup: function(editor) {
        var form = editor.getElement().closest('form');
        if (form) {
            form.addEventListener('submit', function() { editor.save(); });
        }
    }
});
</script>
