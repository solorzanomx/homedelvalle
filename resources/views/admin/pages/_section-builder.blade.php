{{-- Section Builder -- Alpine.js powered page builder --}}
{{-- Pass $page (nullable) for existing sections --}}
@php $existingSections = old('sections_json') ? json_decode(old('sections_json'), true) : ($page->sections ?? []); @endphp

<div x-data="sectionBuilder()" class="card" style="margin-top: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Page Builder</h3>
        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem;">
            <input type="hidden" name="use_sections" value="0">
            <input type="checkbox" name="use_sections" value="1" x-model="enabled"
                   style="width: 16px; height: 16px; accent-color: var(--primary);">
            Usar secciones
        </label>
    </div>
    <div class="card-body" x-show="enabled" x-cloak>
        <input type="hidden" name="sections_json" :value="JSON.stringify(sections)">

        <template x-if="sections.length === 0">
            <div style="text-align: center; padding: 2rem; color: var(--text-muted, #64748b);">
                <p>No hay secciones. Agrega una con el boton de abajo.</p>
            </div>
        </template>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <template x-for="(section, index) in sections" :key="index">
                <div style="border: 1px solid var(--border, #e2e8f0); border-radius: 8px; overflow: hidden;">
                    {{-- Section header --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--bg, #f8fafc); cursor: pointer;"
                         @click="section._open = !section._open">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 0.78rem; color: var(--text-muted, #64748b); font-weight: 600; text-transform: uppercase;"
                                  x-text="sectionLabel(section.type)"></span>
                            <span x-show="section.data?.heading" style="font-size: 0.82rem; color: var(--text);"
                                  x-text="section.data?.heading || ''"></span>
                        </div>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" @click.stop="moveUp(index)" :disabled="index === 0"
                                    style="padding: 0.2rem 0.4rem; font-size: 0.75rem; border: 1px solid var(--border); border-radius: 4px; background: transparent; cursor: pointer;">&uarr;</button>
                            <button type="button" @click.stop="moveDown(index)" :disabled="index === sections.length - 1"
                                    style="padding: 0.2rem 0.4rem; font-size: 0.75rem; border: 1px solid var(--border); border-radius: 4px; background: transparent; cursor: pointer;">&darr;</button>
                            <button type="button" @click.stop="removeSection(index)"
                                    style="padding: 0.2rem 0.4rem; font-size: 0.75rem; border: 1px solid var(--danger, #ef4444); border-radius: 4px; background: transparent; color: var(--danger, #ef4444); cursor: pointer;">&times;</button>
                        </div>
                    </div>

                    {{-- Section body (collapsible) --}}
                    <div x-show="section._open" style="padding: 1rem; border-top: 1px solid var(--border, #e2e8f0);">

                        {{-- HERO --}}
                        <template x-if="section.type === 'hero'">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group"><label class="form-label">Encabezado</label><input type="text" class="form-input" x-model="section.data.heading"></div>
                                <div class="form-group"><label class="form-label">Subtitulo</label><input type="text" class="form-input" x-model="section.data.subheading"></div>
                                <div class="form-group"><label class="form-label">Imagen de fondo (URL)</label><input type="text" class="form-input" x-model="section.data.bg_image" placeholder="/storage/media/..."></div>
                                <div class="form-group"><label class="form-label">Texto del boton</label><input type="text" class="form-input" x-model="section.data.cta_text"></div>
                                <div class="form-group"><label class="form-label">URL del boton</label><input type="text" class="form-input" x-model="section.data.cta_url"></div>
                            </div>
                        </template>

                        {{-- CONTENT (HTML) --}}
                        <template x-if="section.type === 'content'">
                            <div class="form-group">
                                <label class="form-label">Contenido HTML</label>
                                <textarea class="form-textarea" rows="10" x-model="section.data.html" placeholder="HTML del contenido..."></textarea>
                                <p class="form-hint">Puedes pegar HTML o usar el editor WYSIWYG en la pagina de posts.</p>
                            </div>
                        </template>

                        {{-- CTA --}}
                        <template x-if="section.type === 'cta'">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group"><label class="form-label">Encabezado</label><input type="text" class="form-input" x-model="section.data.heading"></div>
                                <div class="form-group"><label class="form-label">Subtitulo</label><input type="text" class="form-input" x-model="section.data.subheading"></div>
                                <div class="form-group"><label class="form-label">Texto del boton</label><input type="text" class="form-input" x-model="section.data.btn_text"></div>
                                <div class="form-group"><label class="form-label">URL del boton</label><input type="text" class="form-input" x-model="section.data.btn_url"></div>
                            </div>
                        </template>

                        {{-- GALLERY --}}
                        <template x-if="section.type === 'gallery'">
                            <div>
                                <label class="form-label">Imagenes (una URL por linea)</label>
                                <textarea class="form-textarea" rows="5" x-model="section.data._imagesText"
                                          @input="section.data.images = $event.target.value.split('\n').filter(u => u.trim()).map(u => ({url: u.trim(), caption: ''}))"
                                          placeholder="/storage/media/img1.jpg&#10;/storage/media/img2.jpg"></textarea>
                                <p class="form-hint">Sube imagenes en la biblioteca de medios y pega las URLs aqui.</p>
                            </div>
                        </template>

                        {{-- CARDS --}}
                        <template x-if="section.type === 'cards'">
                            <div>
                                <div class="form-group"><label class="form-label">Encabezado de seccion</label><input type="text" class="form-input" x-model="section.data.heading"></div>
                                <template x-for="(card, ci) in section.data.items" :key="ci">
                                    <div style="border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                                            <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Icono</label><input type="text" class="form-input" x-model="card.icon" placeholder="&#9733;"></div>
                                            <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Titulo</label><input type="text" class="form-input" x-model="card.title"></div>
                                            <div class="form-group" style="display:flex;align-items:flex-end;"><button type="button" @click="section.data.items.splice(ci, 1)" style="padding:0.4rem 0.6rem;font-size:0.75rem;color:var(--danger);border:1px solid var(--danger);border-radius:4px;background:transparent;cursor:pointer;">&times;</button></div>
                                        </div>
                                        <div class="form-group" style="margin-bottom:0;"><label class="form-label" style="font-size:0.72rem;">Descripcion</label><textarea class="form-textarea" rows="2" x-model="card.description"></textarea></div>
                                    </div>
                                </template>
                                <button type="button" @click="section.data.items.push({icon:'', title:'', description:''})"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Agregar tarjeta</button>
                            </div>
                        </template>

                        {{-- TESTIMONIALS --}}
                        <template x-if="section.type === 'testimonials'">
                            <div>
                                <div class="form-group"><label class="form-label">Encabezado</label><input type="text" class="form-input" x-model="section.data.heading"></div>
                                <template x-for="(test, ti) in section.data.items" :key="ti">
                                    <div style="border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem;">
                                            <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Nombre</label><input type="text" class="form-input" x-model="test.name"></div>
                                            <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Rol</label><input type="text" class="form-input" x-model="test.role"></div>
                                            <div class="form-group" style="display:flex;align-items:flex-end;"><button type="button" @click="section.data.items.splice(ti, 1)" style="padding:0.4rem 0.6rem;font-size:0.75rem;color:var(--danger);border:1px solid var(--danger);border-radius:4px;background:transparent;cursor:pointer;">&times;</button></div>
                                        </div>
                                        <div class="form-group" style="margin-bottom:0;"><label class="form-label" style="font-size:0.72rem;">Texto</label><textarea class="form-textarea" rows="2" x-model="test.text"></textarea></div>
                                    </div>
                                </template>
                                <button type="button" @click="section.data.items.push({name:'', role:'', text:''})"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Agregar testimonio</button>
                            </div>
                        </template>

                        {{-- CONTACT FORM --}}
                        <template x-if="section.type === 'contact_form'">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <div class="form-group"><label class="form-label">Encabezado</label><input type="text" class="form-input" x-model="section.data.heading"></div>
                                <div class="form-group"><label class="form-label">Subtitulo</label><input type="text" class="form-input" x-model="section.data.subheading"></div>
                            </div>
                        </template>

                        {{-- RAW HTML --}}
                        <template x-if="section.type === 'html'">
                            <div class="form-group">
                                <label class="form-label">HTML personalizado</label>
                                <textarea class="form-textarea" rows="10" x-model="section.data.html" style="font-family: monospace; font-size: 0.82rem;"></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Add section menu --}}
        <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <template x-for="type in sectionTypes" :key="type.value">
                <button type="button" @click="addSection(type.value)"
                        style="padding: 0.5rem 0.8rem; font-size: 0.78rem; border: 1px solid var(--border); border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.15s;"
                        onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                    <span x-text="type.icon + ' ' + type.label"></span>
                </button>
            </template>
        </div>
    </div>
</div>

<script>
function sectionBuilder() {
    return {
        enabled: {{ ($page->use_sections ?? false) ? 'true' : 'false' }},
        sections: @json($existingSections ?: []),
        sectionTypes: [
            { value: 'hero', label: 'Hero', icon: '🖼' },
            { value: 'content', label: 'Contenido', icon: '📝' },
            { value: 'cta', label: 'CTA', icon: '🔗' },
            { value: 'gallery', label: 'Galeria', icon: '🖼' },
            { value: 'cards', label: 'Tarjetas', icon: '🃏' },
            { value: 'testimonials', label: 'Testimonios', icon: '💬' },
            { value: 'contact_form', label: 'Formulario', icon: '📨' },
            { value: 'html', label: 'HTML', icon: '⚡' },
        ],
        sectionLabel(type) {
            var t = this.sectionTypes.find(function(s) { return s.value === type; });
            return t ? t.icon + ' ' + t.label : type;
        },
        addSection(type) {
            var data = {};
            switch(type) {
                case 'hero': data = { heading: '', subheading: '', bg_image: '', cta_text: '', cta_url: '' }; break;
                case 'content': data = { html: '' }; break;
                case 'cta': data = { heading: '', subheading: '', btn_text: '', btn_url: '' }; break;
                case 'gallery': data = { images: [], _imagesText: '' }; break;
                case 'cards': data = { heading: '', items: [{ icon: '', title: '', description: '' }] }; break;
                case 'testimonials': data = { heading: '', items: [{ name: '', role: '', text: '' }] }; break;
                case 'contact_form': data = { heading: '', subheading: '' }; break;
                case 'html': data = { html: '' }; break;
            }
            this.sections.push({ type: type, data: data, _open: true });
        },
        removeSection(index) {
            if (confirm('Eliminar esta seccion?')) this.sections.splice(index, 1);
        },
        moveUp(index) {
            if (index > 0) { var tmp = this.sections[index]; this.sections[index] = this.sections[index - 1]; this.sections[index - 1] = tmp; }
        },
        moveDown(index) {
            if (index < this.sections.length - 1) { var tmp = this.sections[index]; this.sections[index] = this.sections[index + 1]; this.sections[index + 1] = tmp; }
        }
    };
}
</script>
