@php $existingFields = old('fields_json') ? json_decode(old('fields_json'), true) : ($form?->fields ?? []); @endphp

<form method="POST" action="{{ $form ? route('admin.forms.update', $form) : route('admin.forms.store') }}" x-data="formBuilder()">
    @csrf
    @if($form) @method('PUT') @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">
        {{-- Main --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Datos del formulario</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" value="{{ old('name', $form?->name) }}" required placeholder="Ej: Formulario de contacto">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-input" value="{{ old('slug', $form?->slug) }}" placeholder="Se genera automaticamente">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Descripcion</label>
                            <textarea name="description" class="form-textarea" rows="2" placeholder="Descripcion del formulario (opcional)">{{ old('description', $form?->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Field builder --}}
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Campos</h3>
                    <span class="text-muted" style="font-size: 0.78rem;" x-text="fields.length + ' campos'"></span>
                </div>
                <div class="card-body">
                    <input type="hidden" name="fields_json" :value="JSON.stringify(fields)">

                    <template x-for="(field, index) in fields" :key="index">
                        <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <span style="font-size: 0.82rem; font-weight: 600;" x-text="field.label || 'Campo sin nombre'"></span>
                                <div style="display: flex; gap: 0.3rem;">
                                    <button type="button" @click="moveUp(index)" :disabled="index === 0" style="padding: 0.2rem 0.4rem; font-size: 0.72rem; border: 1px solid var(--border); border-radius: 4px; background: transparent; cursor: pointer;">&uarr;</button>
                                    <button type="button" @click="moveDown(index)" :disabled="index === fields.length - 1" style="padding: 0.2rem 0.4rem; font-size: 0.72rem; border: 1px solid var(--border); border-radius: 4px; background: transparent; cursor: pointer;">&darr;</button>
                                    <button type="button" @click="fields.splice(index, 1)" style="padding: 0.2rem 0.4rem; font-size: 0.72rem; border: 1px solid var(--danger); border-radius: 4px; background: transparent; color: var(--danger); cursor: pointer;">&times;</button>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                                <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Label</label><input type="text" class="form-input" x-model="field.label"></div>
                                <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Nombre (name)</label><input type="text" class="form-input" x-model="field.name" @input="field.name = $event.target.value.toLowerCase().replace(/[^a-z0-9_]/g, '_')"></div>
                                <div class="form-group">
                                    <label class="form-label" style="font-size:0.72rem;">Tipo</label>
                                    <select class="form-select" x-model="field.type">
                                        <option value="text">Texto</option>
                                        <option value="email">Email</option>
                                        <option value="tel">Telefono</option>
                                        <option value="textarea">Texto largo</option>
                                        <option value="select">Select</option>
                                        <option value="radio">Radio</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="hidden">Oculto</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Placeholder</label><input type="text" class="form-input" x-model="field.placeholder"></div>
                                <div class="form-group" style="display: flex; align-items: flex-end; gap: 1rem;">
                                    <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.82rem; cursor: pointer;">
                                        <input type="checkbox" x-model="field.required" style="accent-color: var(--primary);"> Obligatorio
                                    </label>
                                </div>
                            </div>
                            <template x-if="field.type === 'select' || field.type === 'radio'">
                                <div class="form-group">
                                    <label class="form-label" style="font-size:0.72rem;">Opciones (una por linea: valor|etiqueta)</label>
                                    <textarea class="form-textarea" rows="3" x-model="field._optionsText"
                                              @input="field.options = $event.target.value.split('\n').filter(o => o.trim()).map(o => { var p = o.split('|'); return {value: p[0].trim(), label: (p[1] || p[0]).trim()}; })"
                                              placeholder="opcion1|Opcion 1&#10;opcion2|Opcion 2"></textarea>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" @click="addField('text')" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Texto</button>
                        <button type="button" @click="addField('email')" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Email</button>
                        <button type="button" @click="addField('tel')" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Telefono</button>
                        <button type="button" @click="addField('textarea')" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Texto largo</button>
                        <button type="button" @click="addField('select')" style="padding: 0.4rem 0.8rem; font-size: 0.78rem; border: 1px dashed var(--border); border-radius: 6px; background: transparent; cursor: pointer;">+ Select</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Configuracion</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $form?->is_active ?? true) ? 'checked' : '' }}
                                   style="width: 16px; height: 16px; accent-color: var(--primary);">
                            Formulario activo
                        </label>
                    </div>
                    @if($form)
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.5rem;">
                        <p>URL publica: <code>/form/{{ $form->slug }}</code></p>
                        <p>Envios: {{ $form->submissions_count }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="form-actions" style="border: none; padding-top: 0;">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $form ? 'Guardar' : 'Crear' }}</button>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
function formBuilder() {
    return {
        fields: @json($existingFields),
        addField: function(type) {
            this.fields.push({
                name: type + '_' + (this.fields.length + 1),
                label: '',
                type: type,
                required: false,
                placeholder: '',
                options: [],
                _optionsText: ''
            });
        },
        moveUp: function(i) { if (i > 0) { var t = this.fields[i]; this.fields[i] = this.fields[i-1]; this.fields[i-1] = t; } },
        moveDown: function(i) { if (i < this.fields.length - 1) { var t = this.fields[i]; this.fields[i] = this.fields[i+1]; this.fields[i+1] = t; } }
    };
}
</script>
@endsection
