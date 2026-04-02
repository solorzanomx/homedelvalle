@if($property->description)
<div class="mt-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Descripción</h2>
    <div class="mt-4 text-gray-600 leading-relaxed prose prose-sm max-w-none prose-a:text-brand-600 prose-blockquote:border-l-brand-500">
        {!! nl2br(e($property->description)) !!}
    </div>
</div>
@endif
