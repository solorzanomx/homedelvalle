@php $d = $data; @endphp
<section class="py-16 sm:py-20 bg-gray-50/60">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        @if(!empty($d['heading']))
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $d['heading'] }}</h2>
            @if(!empty($d['subheading']))
            <p class="mt-3 text-gray-500">{{ $d['subheading'] }}</p>
            @endif
        </div>
        @endif
        <div class="rounded-2xl border border-gray-200/60 bg-white p-8 shadow-premium-lg">
            <x-public.contact-form />
        </div>
    </div>
</section>
