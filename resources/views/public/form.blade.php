@extends('layouts.public')

@section('meta')
    <x-public.seo-meta :title="$form->name" :canonical="url('/form/' . $form->slug)" />
@endsection

@section('content')
    <x-public.hero :heading="$form->name" :subheading="$form->description ?? ''" :breadcrumb-items="[['label' => $form->name]]" />

    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
            <div class="rounded-2xl bg-green-50 border border-green-200 p-6 text-center mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="text-3xl mb-2">&#10004;</div>
                <p class="text-green-800 font-semibold">{{ session('success') }}</p>
            </div>
            @else
            <div class="rounded-2xl border border-gray-200/60 p-8 shadow-premium-lg" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <form method="POST" action="{{ route('form.submit', $form->slug) }}">
                    @csrf
                    @if(request('utm_source'))<input type="hidden" name="utm_source" value="{{ request('utm_source') }}">@endif
                    @if(request('utm_medium'))<input type="hidden" name="utm_medium" value="{{ request('utm_medium') }}">@endif
                    @if(request('utm_campaign'))<input type="hidden" name="utm_campaign" value="{{ request('utm_campaign') }}">@endif

                    @foreach($form->fields as $field)
                        @if(($field['type'] ?? 'text') === 'hidden')
                            <input type="hidden" name="field_{{ $field['name'] }}" value="{{ $field['placeholder'] ?? '' }}">
                            @continue
                        @endif

                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ $field['label'] ?? $field['name'] }}
                                @if(!empty($field['required'])) <span class="text-red-500">*</span> @endif
                            </label>

                            @switch($field['type'] ?? 'text')
                                @case('textarea')
                                    <textarea name="field_{{ $field['name'] }}" rows="4" placeholder="{{ $field['placeholder'] ?? '' }}"
                                              class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition"
                                              {{ !empty($field['required']) ? 'required' : '' }}>{{ old('field_' . $field['name']) }}</textarea>
                                    @break
                                @case('select')
                                    <select name="field_{{ $field['name'] }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition" {{ !empty($field['required']) ? 'required' : '' }}>
                                        <option value="">{{ $field['placeholder'] ?? 'Seleccionar...' }}</option>
                                        @foreach($field['options'] ?? [] as $opt)
                                        <option value="{{ $opt['value'] ?? '' }}" {{ old('field_' . $field['name']) === ($opt['value'] ?? '') ? 'selected' : '' }}>{{ $opt['label'] ?? $opt['value'] ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('radio')
                                    <div class="space-y-2 mt-1">
                                        @foreach($field['options'] ?? [] as $opt)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input type="radio" name="field_{{ $field['name'] }}" value="{{ $opt['value'] ?? '' }}" {{ old('field_' . $field['name']) === ($opt['value'] ?? '') ? 'checked' : '' }} class="accent-brand-500">
                                            {{ $opt['label'] ?? $opt['value'] ?? '' }}
                                        </label>
                                        @endforeach
                                    </div>
                                    @break
                                @case('checkbox')
                                    <label class="flex items-center gap-2 text-sm cursor-pointer mt-1">
                                        <input type="checkbox" name="field_{{ $field['name'] }}" value="1" {{ old('field_' . $field['name']) ? 'checked' : '' }} class="accent-brand-500">
                                        {{ $field['placeholder'] ?? '' }}
                                    </label>
                                    @break
                                @default
                                    <input type="{{ $field['type'] ?? 'text' }}" name="field_{{ $field['name'] }}" value="{{ old('field_' . $field['name']) }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 transition"
                                           {{ !empty($field['required']) ? 'required' : '' }}>
                            @endswitch

                            @error('field_' . ($field['name'] ?? ''))
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <button type="submit" class="w-full rounded-xl gradient-brand px-6 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 mt-2">
                        Enviar
                    </button>
                </form>
            </div>
            @endif
        </div>
    </section>
@endsection
