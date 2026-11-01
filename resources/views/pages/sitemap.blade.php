<?php echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <xhtml:link rel="alternate" hreflang="pt-BR" href="{{ $url['loc'] }}"/>
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>{{ $url['frequency'] }}</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
