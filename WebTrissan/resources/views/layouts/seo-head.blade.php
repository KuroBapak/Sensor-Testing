@php
  $seoUrl = url()->current();
  $seoImage = ($globalSettings ?? null)?->navbar_logo_url;
  $seoSiteName = ($globalSettings ?? null)?->company_name;
@endphp
<link rel="canonical" href="{{ $seoUrl }}">
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
@endif
<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
@if(filled($seoDescription ?? null))
<meta name="twitter:description" content="{{ $seoDescription }}">
@endif
@if($seoImage)
<meta name="twitter:image" content="{{ $seoImage }}">
@endif
@if(filled($structuredData ?? null))
<script type="application/ld+json">{!! $structuredData !!}</script>
@endif
