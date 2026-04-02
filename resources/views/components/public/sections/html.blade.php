@php $d = $data; @endphp
@if(!empty($d['html']))
<section class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {!! $d['html'] !!}
    </div>
</section>
@endif
