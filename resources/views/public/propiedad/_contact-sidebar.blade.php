<div class="rounded-2xl border border-gray-200/60 p-6 shadow-premium-lg" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <h3 class="text-base font-bold text-gray-900 mb-5">¿Te interesa esta propiedad?</h3>
    <x-public.contact-form :property-id="$property->id" :compact="true" source="property" />
</div>
