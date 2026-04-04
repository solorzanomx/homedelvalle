<div class="mt-5 flex flex-wrap gap-2.5" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <a href="https://wa.me/?text={{ urlencode($property->title . ' - ' . route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug])) }}" target="_blank" rel="noopener noreferrer"
       class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-[#25D366]/10 hover:text-[#25D366] transition-all duration-300 text-xs font-medium" aria-label="WhatsApp">
        <x-icon name="brands/whatsapp" class="w-3.5 h-3.5" />
        WhatsApp
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug])) }}" target="_blank" rel="noopener noreferrer"
       class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300 text-xs font-medium" aria-label="Facebook">
        <x-icon name="brands/facebook" class="w-3.5 h-3.5" />
        Facebook
    </a>
    <button onclick="navigator.clipboard.writeText('{{ route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug]) }}'); this.querySelector('span').textContent = '¡Copiado!'; setTimeout(() => this.querySelector('span').textContent = 'Copiar enlace', 2000)"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all duration-300 text-xs font-medium">
        <x-icon name="share-2" class="w-3.5 h-3.5" />
        <span>Copiar enlace</span>
    </button>
</div>
