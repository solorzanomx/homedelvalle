<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Static pages --}}
    @foreach($staticPages as $page)
    <url>
        <loc>{{ $page['url'] }}</loc>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>
    </url>
    @endforeach

    {{-- Blog posts --}}
    @foreach($posts as $post)
    <url>
        <loc>{{ url('/blog/' . $post->slug) }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Properties --}}
    @foreach($properties as $property)
    <url>
        <loc>{{ url('/propiedades/' . $property->id) }}</loc>
        <lastmod>{{ $property->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Colonia landing pages --}}
    @foreach($colonias ?? [] as $colonia)
    <url>
        <loc>{{ url('/' . $colonia->slug) }}</loc>
        <lastmod>{{ $colonia->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Precios hub --}}
    <url>
        <loc>{{ url('/precios') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
    </url>
    <url>
        <loc>{{ url('/precios/opinion-de-valor') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    {{-- Market zones (/precios) --}}
    @foreach($marketZones ?? [] as $zone)
    <url>
        <loc>{{ url('/precios/' . $zone->slug) }}</loc>
        <lastmod>{{ $zone->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.85</priority>
    </url>
    @foreach($zone->publishedColonias as $colonia)
    <url>
        <loc>{{ url('/precios/' . $zone->slug . '/' . $colonia->slug) }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
    @endforeach

</urlset>
