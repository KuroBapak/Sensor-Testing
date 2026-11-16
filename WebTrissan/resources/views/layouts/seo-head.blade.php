@php
  $seoSettings = $globalSettings ?? null;
  $seoUrl = url()->current();
  $seoShare = \App\Support\BrandImages::social($seoSettings);
  $seoImage = $seoShare['url'] ?? $seoSettings?->navbar_logo_url;
  $seoSiteName = $seoSettings?->company_name;
  $faviconUrl = $seoSettings?->favicon_url;
  $faviconType = [
    'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
    'webp' => 'image/webp', 'ico' => 'image/x-icon', 'svg' => 'image/svg+xml',
  ][strtolower(pathinfo((string) parse_url((string) $faviconUrl, PHP_URL_PATH), PATHINFO_EXTENSION))] ?? null;
  // Without an icon uploaded in the CMS, square icons made from the logo are used instead of the wide logo itself.
  $generatedIcon = blank($seoSettings?->favicon) ? \App\Support\BrandImages::icon($seoSettings, 48) : null;
  $generatedTouchIcon = $generatedIcon ? \App\Support\BrandImages::icon($seoSettings, 180, '#FFFFFF') : null;
@endphp
@if($generatedIcon && $generatedTouchIcon)
<link rel="icon" href="{{ $generatedIcon }}" type="image/png" sizes="48x48">
<link rel="apple-touch-icon" href="{{ $generatedTouchIcon }}" sizes="180x180">
@elseif($faviconUrl)
<link rel="icon" href="{{ $faviconUrl }}"@if($faviconType) type="{{ $faviconType }}"@endif>
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@endif
<link rel="canonical" href="{{ $seoUrl }}">
@if(! config('seo.indexable'))
<meta name="robots" content="noindex, nofollow">
@elseif($seoNoindex ?? false)
<meta name="robots" content="noindex, follow">
@endif
<meta property="og:type" content="website">
<meta property="og:locale" content="id_ID">
@if(filled($seoSiteName))
<meta property="og:site_name" content="{{ $seoSiteName }}">
@endif
<meta property="og:title" content="{{ $seoTitle }}">
@if(filled($seoDescription ?? null))
<meta property="og:description" content="{{ $seoDescription }}">
@endif
<meta property="og:url" content="{{ $seoUrl }}">
@if($seoImage)
<meta property="og:image" content="{{ $seoImage }}">
@if($seoShare)
<meta property="og:image:width" content="{{ $seoShare['width'] }}">
<meta property="og:image:height" content="{{ $seoShare['height'] }}">
@if(filled($seoSiteName))
<meta property="og:image:alt" content="{{ $seoSiteName }}">
@endif
@endif
@endif
<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
@if(filled($seoDescription ?? null))
<meta name="twitter:description" content="{{ $seoDescription }}">
@endif
@if($seoImage)
<meta name="twitter:image" content="{{ $seoImage }}">
@if($seoShare && filled($seoSiteName))
<meta name="twitter:image:alt" content="{{ $seoSiteName }}">
@endif
@endif
@if(filled($structuredData ?? null))
<script type="application/ld+json">{!! $structuredData !!}</script>
@endif
