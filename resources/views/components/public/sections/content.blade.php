@php $d = $data; @endphp
@if(!empty($d['html']))
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none prose-headings:font-extrabold prose-headings:tracking-tight prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline prose-blockquote:border-brand-500 prose-img:rounded-xl">
            {!! $d['html'] !!}
        </div>
    </div>
</section>
@endif
