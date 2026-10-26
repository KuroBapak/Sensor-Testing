<!DOCTYPE html><html class="scroll-smooth" lang="id" style=""><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>{{ $pageSettings?->seo_title ?? 'Lisensi & Sertifikasi | PT Apex Industrial Engineering' }}</title>
@if(!empty($pageSettings?->seo_description))
<meta name="description" content="{{ $pageSettings->seo_description }}">
@endif
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Google Fonts: Montserrat & Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&amp;family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
          "on-primary": "#ffffff",
          "on-surface": "#0b1c30",
          "on-primary-container": "#7689a4",
          "on-error-container": "#93000a",
          "outline": "#74777d",
          "outline-variant": "#c4c6cd",
          "surface-tint": "#4d6079",
          "secondary": "#416279",
          "inverse-on-surface": "#eaf1ff",
          "error-container": "#ffdad6",
          "background": "#f8f9ff",
          "surface-dim": "#cbdbf5",
          "on-secondary-fixed-variant": "#284a60",
          "inverse-primary": "#b5c8e5",
          "surface-container-lowest": "#ffffff",
          "primary-fixed-dim": "#b5c8e5",
          "on-primary-fixed": "#081c32",
          "surface": "#f8f9ff",
          "on-tertiary-fixed-variant": "#39485a",
          "on-primary-fixed-variant": "#364860",
          "tertiary": "#000916",
          "inverse-surface": "#213145",
          "surface-variant": "#d3e4fe",
          "tertiary-fixed": "#d4e4fa",
          "primary": "#000917",
          "surface-container-low": "#eff4ff",
          "surface-bright": "#f8f9ff",
          "on-tertiary-fixed": "#0d1c2d",
          "primary-fixed": "#d2e4ff",
          "primary-container": "#0d2137",
          "secondary-container": "#c2e4ff",
          "on-secondary-container": "#45667d",
          "surface-container-high": "#dce9ff",
          "on-tertiary-container": "#7a899d",
          "tertiary-container": "#122131",
          "on-background": "#0b1c30",
          "secondary-fixed-dim": "#a9cbe5",
          "on-error": "#ffffff",
          "on-tertiary": "#ffffff",
          "tertiary-fixed-dim": "#b9c8de",
          "secondary-fixed": "#c8e6ff",
          "on-surface-variant": "#44474d",
          "surface-container-highest": "#d3e4fe",
          "surface-container": "#e5eeff",
          "error": "#ba1a1a",
          "on-secondary": "#ffffff",
          "on-secondary-fixed": "#001e2e"
        },
        "borderRadius": {
          "DEFAULT": "0.125rem",
          "lg": "0.25rem",
          "xl": "0.5rem",
          "full": "0.75rem"
        },
        "spacing": {
          "space-sm": "0.5rem",
          "space-lg": "1.5rem",
          "margin": "1rem",
          "space-md": "1rem",
          "space-2xl": "4rem",
          "margin-desktop": "4rem",
          "space-xl": "2.5rem",
          "space-xs": "0.25rem",
          "gutter-desktop": "2rem",
          "margin-tablet": "2.5rem",
          "gutter": "1.5rem",
          "space-3xl": "6rem"
        },
        "fontFamily": {
          "body-lg": [
            "Inter"
          ],
          "label-sm": [
            "Inter"
          ],
          "headline-lg": [
            "Montserrat"
          ],
          "label-md": [
            "Inter"
          ],
          "body-md": [
            "Inter"
          ],
          "headline-md": [
            "Montserrat"
          ],
          "body-sm": [
            "Inter"
          ],
          "display-hero-mobile": [
            "Montserrat"
          ],
          "display-hero": [
            "Montserrat"
          ],
          "title-md": [
            "Inter"
          ],
          "headline-sm": [
            "Montserrat"
          ],
          "label-caps": [
            "Montserrat"
          ],
          "headline-lg-mobile": [
            "Montserrat"
          ]
        },
        "fontSize": {
          "body-lg": [
            "18px",
            {
              "lineHeight": "28px",
              "fontWeight": "400"
            }
          ],
          "label-sm": [
            "12px",
            {
              "lineHeight": "16px",
              "fontWeight": "500"
            }
          ],
          "headline-lg": [
            "40px",
            {
              "lineHeight": "48px",
              "letterSpacing": "-0.015em",
              "fontWeight": "700"
            }
          ],
          "label-md": [
            "14px",
            {
              "lineHeight": "20px",
              "fontWeight": "500"
            }
          ],
          "body-md": [
            "16px",
            {
              "lineHeight": "24px",
              "fontWeight": "400"
            }
          ],
          "headline-md": [
            "28px",
            {
              "lineHeight": "36px",
              "letterSpacing": "-0.01em",
              "fontWeight": "700"
            }
          ],
          "body-sm": [
            "14px",
            {
              "lineHeight": "20px",
              "fontWeight": "400"
            }
          ],
          "display-hero-mobile": [
            "36px",
            {
              "lineHeight": "44px",
              "letterSpacing": "-0.02em",
              "fontWeight": "800"
            }
          ],
          "display-hero": [
            "56px",
            {
              "lineHeight": "64px",
              "letterSpacing": "-0.02em",
              "fontWeight": "800"
            }
          ],
          "title-md": [
            "18px",
            {
              "lineHeight": "26px",
              "fontWeight": "600"
            }
          ],
          "headline-sm": [
            "20px",
            {
              "lineHeight": "28px",
              "fontWeight": "600"
            }
          ],
          "label-caps": [
            "12px",
            {
              "lineHeight": "16px",
              "letterSpacing": "0.08em",
              "fontWeight": "700"
            }
          ],
          "headline-lg-mobile": [
            "28px",
            {
              "lineHeight": "36px",
              "letterSpacing": "-0.01em",
              "fontWeight": "700"
            }
          ]
        }
      },
    },
  }
</script>
<style>
  @layer base {
    html {
      scroll-behavior: smooth;
    }
    body {
      overscroll-behavior-y: none;
    }
  }
</style>
<meta content="web_standard" name="shell-type">
</head>
<body class="bg-surface text-on-surface antialiased relative selection:bg-primary-container selection:text-on-primary">
@include('layouts.navbar')

<main class="relative"><div class="flex flex-col w-full">
<!-- SECTION 1: HEADER & COMPLIANCE METRICS -->
<section class="relative w-full bg-surface-container-low overflow-hidden pt-8 pb-6 md:pt-16 md:pb-12"><div class="absolute inset-0 opacity-40 pointer-events-none bg-[radial-gradient(#416279_1px,transparent_1px)] [background-size:24px_24px]"></div><div class="relative max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop"><div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 md:gap-8 mb-6"><div class="max-w-3xl"><nav aria-label="Breadcrumb" class="flex items-center gap-2 mb-3 md:mb-4 font-body-sm text-body-sm"><a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary transition"><span class="material-symbols-outlined text-sm">home</span><span class="">Beranda</span></a><span class="text-outline text-xs">/</span><span class="text-primary font-semibold">License &amp; Certification</span></nav><h1 class="font-display-hero text-headline-lg-mobile md:text-display-hero text-primary tracking-tight leading-tight">{{ $pageSettings?->hero_title ?? 'License & Certification' }}</h1><p class="font-body-md md:font-body-lg text-body-md md:text-body-lg text-on-surface-variant mt-2 md:mt-4 leading-relaxed">{{ $pageSettings?->hero_subtitle ?? 'Portofolio Lisensi, Akreditasi, dan Sertifikasi Kepatuhan Mutu yang Dimiliki oleh PT Apex Industrial Engineering untuk Menjamin Integritas Rekayasa & Standar Manufaktur Global.' }}</p><div class="flex flex-wrap items-center gap-2.5 md:gap-3 mt-4 md:mt-6"><a href="#product-certs" class="inline-flex items-center gap-1.5 md:gap-2 px-3.5 md:px-4 py-2 bg-primary text-on-primary hover:bg-secondary rounded font-label-md text-label-md font-semibold transition shadow-sm text-xs md:text-sm"><span class="material-symbols-outlined text-sm">precision_manufacturing</span>Sertifikasi Produk Manufaktur</a><a href="#corporate-certs" class="inline-flex items-center gap-1.5 md:gap-2 px-3.5 md:px-4 py-2 bg-surface-container hover:bg-surface-container-high text-primary rounded font-label-md text-label-md font-semibold transition text-xs md:text-sm"><span class="material-symbols-outlined text-sm">domain</span>Akreditasi Fasilitas &amp; Korporat</a></div></div><div class="flex items-center gap-3 md:gap-4 bg-surface-container-lowest p-3 md:p-4 rounded-xl shadow-sm border-l-4 border-l-secondary"><div class="w-10 h-10 md:w-12 md:h-12 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0"><span class="material-symbols-outlined text-lg md:text-2xl">verified</span></div><div><div class="font-label-caps text-label-caps text-secondary uppercase text-xs">{{ $pageSettings?->hero_badge_card['eyebrow'] ?? 'Integritas Mutu Pabrik' }}</div><div class="font-title-md text-sm md:text-title-md text-primary font-bold">{{ $pageSettings?->hero_badge_card['title'] ?? 'Fasilitas Terakreditasi Penuh' }}</div><div class="font-body-sm text-xs md:text-body-sm text-on-surface-variant">{{ $pageSettings?->hero_badge_card['caption'] ?? 'Standar Internasional ISO & ASME' }}</div></div></div></div><div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 pt-4 md:pt-6 border-t border-outline-variant/30">
@if(!empty($pageSettings?->stats))
  @foreach($pageSettings->stats as $stat)
  <div class="bg-surface-container-lowest p-3.5 md:p-5 rounded-lg shadow-sm">
    <div class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary tracking-tight font-extrabold">{{ $stat['value'] ?? '' }}</div>
    <div class="h-0.5 w-8 md:w-12 bg-secondary my-1.5 md:my-2"></div>
    <div class="font-title-md text-sm md:text-title-md font-bold text-primary">{{ $stat['label'] ?? '' }}</div>
    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase mt-0.5 md:mt-1 block text-xs">{{ $stat['caption'] ?? '' }}</span>
  </div>
  @endforeach
@else
<div class="bg-surface-container-lowest p-3.5 md:p-5 rounded-lg shadow-sm"><div class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary tracking-tight font-extrabold">4+</div><div class="h-0.5 w-8 md:w-12 bg-secondary my-1.5 md:my-2"></div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">Sertifikasi Produk</div><span class="font-label-caps text-label-caps text-on-surface-variant uppercase mt-0.5 md:mt-1 block text-xs">CE, ATEX, IECEx &amp; API</span></div><div class="bg-surface-container-lowest p-3.5 md:p-5 rounded-lg shadow-sm"><div class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary tracking-tight font-extrabold">6</div><div class="h-0.5 w-8 md:w-12 bg-secondary my-1.5 md:my-2"></div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">QMS &amp; Aerospace</div><span class="font-label-caps text-label-caps text-on-surface-variant uppercase mt-0.5 md:mt-1 block text-xs">ISO 9001 &amp; AS9100 Rev D</span></div><div class="bg-surface-container-lowest p-3.5 md:p-5 rounded-lg shadow-sm"><div class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary tracking-tight font-extrabold">100%</div><div class="h-0.5 w-8 md:w-12 bg-secondary my-1.5 md:my-2"></div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">Bejana Tekan &amp; Offshore</div><span class="font-label-caps text-label-caps text-on-surface-variant uppercase mt-0.5 md:mt-1 block text-xs">ASME 'U' &amp; 'R' Stamp, DNV GL</span></div><div class="bg-surface-container-lowest p-3.5 md:p-5 rounded-lg shadow-sm"><div class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary tracking-tight font-extrabold">7</div><div class="h-0.5 w-8 md:w-12 bg-secondary my-1.5 md:my-2"></div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">K3, Lingkungan &amp; Otomotif</div><span class="font-label-caps text-label-caps text-on-surface-variant uppercase mt-0.5 md:mt-1 block text-xs">ISO 14001, 45001 &amp; IATF 16949</span></div>
@endif
</div></div></section>
<!-- SECTION 2: FEATURED CORE CERTIFICATIONS -->
<!-- SECTION 3: DOCUMENT REGISTRY & VERIFICATION TABLE -->
<section id="corporate-certs" class="w-full scroll-mt-20 bg-surface-container-lowest pt-10 pb-8 md:pt-20 md:pb-16"><div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop"><div class="flex flex-col md:flex-row md:items-end justify-between mb-6 md:mb-8"><div><div class="font-label-caps text-label-caps text-secondary uppercase tracking-widest">Corporate &amp; Facility Accreditation</div><h2 class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary mt-1">Sertifikasi Perusahaan &amp; Fasilitas Fabrikasi</h2></div><p class="font-body-sm text-body-sm text-on-surface-variant max-w-md mt-2 md:mt-0">Akreditasi sistem manajemen mutu menyeluruh, standar fasilitas bengkel manufaktur, keselamatan operasional, dan kepatuhan lingkungan internasional.</p></div><div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
@if(!empty($pageSettings?->corporate_highlight_cards))
  @foreach($pageSettings->corporate_highlight_cards as $card)
  <div class="bg-surface-container-lowest p-3 md:p-5 rounded-xl border border-outline-variant/30 flex flex-col sm:flex-row items-start gap-2.5 md:gap-4 shadow-sm">
    <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary shrink-0">
      <span class="material-symbols-outlined text-sm md:text-base">{{ $card['icon'] ?? 'verified' }}</span>
    </div>
    <div>
      <div class="font-title-md text-sm md:text-title-md font-bold text-primary">{{ $card['title'] ?? '' }}</div>
      <div class="font-label-sm text-xs md:text-label-sm text-secondary font-medium">{{ $card['subtitle'] ?? '' }}</div>
      <div class="text-xs text-on-surface-variant mt-0.5 md:mt-1">{{ $card['caption'] ?? '' }}</div>
    </div>
  </div>
  @endforeach
@else
<div class="bg-surface-container-lowest p-3 md:p-5 rounded-xl border border-outline-variant/30 flex flex-col sm:flex-row items-start gap-2.5 md:gap-4 shadow-sm"><div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary shrink-0"><span class="material-symbols-outlined text-sm md:text-base">verified</span></div><div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">ISO 9001:2015</div><div class="font-label-sm text-xs md:text-label-sm text-secondary font-medium">QMS Fabrikasi</div><div class="text-xs text-on-surface-variant mt-0.5 md:mt-1">TÜV SÜD · Audit 2025</div></div></div><div class="bg-surface-container-lowest p-3 md:p-5 rounded-xl border border-outline-variant/30 flex flex-col sm:flex-row items-start gap-2.5 md:gap-4 shadow-sm"><div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-secondary-container flex items-center justify-center text-on-secondary-container shrink-0"><span class="material-symbols-outlined text-sm md:text-base">flight</span></div><div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">AS9100 Rev D</div><div class="font-label-sm text-xs md:text-label-sm text-secondary font-medium">Aerospace QMS</div><div class="text-xs text-on-surface-variant mt-0.5 md:mt-1">Bureau Veritas · Tier-1</div></div></div><div class="bg-surface-container-lowest p-3 md:p-5 rounded-xl border border-outline-variant/30 flex flex-col sm:flex-row items-start gap-2.5 md:gap-4 shadow-sm"><div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-primary shrink-0"><span class="material-symbols-outlined text-sm md:text-base">local_fire_department</span></div><div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">ASME 'U' &amp; 'R'</div><div class="font-label-sm text-xs md:text-label-sm text-secondary font-medium">Pressure Vessel</div><div class="text-xs text-on-surface-variant mt-0.5 md:mt-1">ASME Sec. VIII</div></div></div><div class="bg-surface-container-lowest p-3 md:p-5 rounded-xl border border-outline-variant/30 flex flex-col sm:flex-row items-start gap-2.5 md:gap-4 shadow-sm"><div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary shrink-0"><span class="material-symbols-outlined text-sm md:text-base">health_and_safety</span></div><div><div class="font-title-md text-sm md:text-title-md font-bold text-primary">ISO 14001/45001</div><div class="font-label-sm text-xs md:text-label-sm text-secondary font-medium">K3 &amp; Lingkungan</div><div class="text-xs text-on-surface-variant mt-0.5 md:mt-1">DNV GL &amp; SGS</div></div></div>
@endif
</div><div class="mb-4 md:mb-6 bg-surface-container-lowest p-3 md:p-6 rounded-xl shadow-md border border-outline-variant/30"><div class="flex flex-col md:flex-row gap-3 md:gap-4 items-stretch md:items-center justify-between"><div class="relative flex-1 min-w-[260px]"><span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline">search</span><input class="w-full pl-11 pr-4 py-2 md:py-2.5 bg-surface rounded text-body-sm font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary transition text-xs md:text-sm" id="certSearchInput" oninput="handleCertFilter()" placeholder="Cari kode lisensi, nama standar, atau badan akreditasi..." type="text"></div><div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-none" id="categoryButtonsContainer">
<button class="cert-category-filter px-3 md:px-4 py-1.5 md:py-2 rounded font-label-md text-xs md:text-label-md transition bg-primary text-on-primary whitespace-nowrap" id="btn-cat-all" onclick="setCertCategory('all')" type="button">Semua Kategori</button>
@foreach($certificationCategories as $category)
<button class="cert-category-filter px-3 md:px-4 py-1.5 md:py-2 rounded font-label-md text-xs md:text-label-md transition bg-surface-container text-on-surface-variant hover:text-primary whitespace-nowrap" id="btn-cat-{{ $category->slug }}" data-cat="{{ $category->slug }}" onclick="setCertCategory(this.dataset.cat)" type="button">{{ $category->name }}</button>
@endforeach
</div></div></div><div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-left border-collapse min-w-[620px] md:min-w-full"><thead><tr class="bg-surface-container text-on-surface-variant font-label-caps text-label-caps uppercase"><th class="py-3 md:py-4 px-4 md:px-6 text-xs">Standar Perusahaan &amp; Dokumen</th><th class="py-3 md:py-4 px-4 md:px-6 text-xs">Badan Akreditasi</th><th class="py-3 md:py-4 px-4 md:px-6 text-xs">ID / Registrasi</th><th class="py-3 md:py-4 px-4 md:px-6 text-xs">Masa Berlaku</th><th class="py-3 md:py-4 px-4 md:px-6 text-right text-xs">Gambar Sertifikasi</th></tr></thead><tbody class="divide-y divide-outline-variant/30 font-body-sm text-body-sm text-on-surface">
@foreach($corporateAccreditations as $accreditation)
@php
  $modalTitle = $accreditation->modal_title ?: trim($accreditation->standard_name.' '.$accreditation->standard_description);
  $searchBlob = \Illuminate\Support\Str::lower(implode(' ', array_filter([
    $accreditation->standard_name, $accreditation->standard_description, $accreditation->accreditation_body,
    $accreditation->registration_id, $accreditation->certificationCategory?->name,
  ])));
@endphp
<tr class="cert-row hover:bg-surface-container-low/60 transition" data-category="{{ $accreditation->certificationCategory?->slug }}" data-search="{{ $searchBlob }}" data-title="{{ $modalTitle }}" data-reg="{{ $accreditation->registration_id }}" data-body="{{ $accreditation->accreditation_body }}" data-validity="{{ $accreditation->validity_label }}" data-scope="{{ $accreditation->scope_detail }}" data-image="{{ $accreditation->certificate_image_url }}">
<td class="py-3 md:py-4 px-4 md:px-6"><div class="font-title-md text-sm md:text-title-md text-primary font-semibold">{{ $accreditation->standard_name }}</div><div class="text-xs text-on-surface-variant">{{ $accreditation->standard_description }}</div></td>
<td class="py-3 md:py-4 px-4 md:px-6 text-xs md:text-sm">{{ $accreditation->accreditation_body }}</td>
<td class="py-3 md:py-4 px-4 md:px-6 font-mono text-xs text-primary font-medium">{{ $accreditation->registration_id }}</td>
<td class="py-3 md:py-4 px-4 md:px-6 text-xs md:text-sm">{{ $accreditation->validity_label ?? '-' }}</td>
<td class="py-3 md:py-4 px-4 md:px-6 text-right"><button class="cert-detail-btn inline-flex items-center gap-1.5 px-2.5 md:px-3 py-1 md:py-1.5 rounded bg-surface-container hover:bg-surface-container-high text-primary font-label-sm text-xs md:text-label-sm transition shadow-sm whitespace-nowrap" type="button"><span class="material-symbols-outlined text-sm">visibility</span> Show Image</button></td>
</tr>
@endforeach
<tr class="hidden" id="certEmptyRow"><td class="py-8 px-6 text-center text-on-surface-variant text-sm" colspan="5">Tidak ada dokumen akreditasi yang sesuai dengan pencarian atau kategori ini.</td></tr>
</tbody></table></div><div class="flex flex-col sm:flex-row items-center justify-between gap-3 md:gap-4 px-4 md:px-6 py-3 md:py-4 border-t border-outline-variant/30 bg-surface-container-lowest"><div class="flex items-center gap-2 text-xs md:text-body-sm font-body-sm text-on-surface-variant"><span class="inline-flex items-center px-2 py-0.5 rounded bg-surface-container text-xs font-medium text-secondary">Total <span id="certTotal">{{ $corporateAccreditations->count() }}</span> Dokumen</span><span class="hidden sm:inline" id="certRangeInfo"></span></div><nav aria-label="Navigasi Halaman Akreditasi" class="flex items-center gap-1.5" id="certPagination"></nav></div></div></div></section><section id="product-certs" class="w-full scroll-mt-20 bg-surface-container-low pt-10 pb-16 md:pt-16 md:pb-28"><div class="max-w-7xl mx-auto px-margin-tablet lg:px-margin-desktop"><div class="flex flex-col md:flex-row md:items-end justify-between mb-6 md:mb-space-xl"><div><div class="font-label-caps text-label-caps text-secondary uppercase tracking-widest">Product-Level Compliance</div><h2 class="font-headline-md md:font-headline-lg text-headline-md md:text-headline-lg text-primary mt-1">Sertifikasi Khusus Produk Manufaktur</h2></div><p class="font-body-sm text-body-sm text-on-surface-variant max-w-md mt-2 md:mt-0">Dokumentasi sertifikasi uji tipe, sertifikat kepatuhan standar industri (CE, ATEX, ASME, ISO), dan uji ketahanan per komponen manufaktur.</p></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
@foreach($productCertifications as $certification)
@php
  $anchor = str_starts_with($certification->slug, 'cert-') ? $certification->slug : 'cert-'.$certification->slug;
  $accentClasses = ['bg-primary', 'bg-secondary', 'bg-primary-container'];
  $specRows = array_slice($certification->spec_rows ?? [], 0, 4);
@endphp
<article id="{{ $anchor }}" class="cert-card flex flex-col justify-between bg-surface-container-lowest rounded-xl p-5 md:p-8 shadow-sm hover:shadow-md border border-outline-variant/30 relative transition-all duration-300 scroll-mt-28" data-title="{{ $certification->product_name }}" data-reg="{{ $certification->certificate_number }}" data-body="{{ $certification->issuing_body }}" data-validity="{{ $certification->validity_label }}" data-scope="{{ $certification->scope_description }}" data-image="{{ $certification->certificate_image_url }}" data-pdf="{{ $certification->certificate_pdf_url }}">
<div class="absolute top-0 left-0 right-0 h-1.5 {{ $accentClasses[$loop->index % 3] }}"></div>
<div>
<div class="flex flex-wrap items-center justify-between gap-2 mb-4">
<div class="flex items-center gap-1.5">
@if($certification->category_tag)
<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-primary rounded font-label-sm text-xs md:text-label-sm font-semibold"><span class="material-symbols-outlined text-xs">{{ $certification->category_icon ?: 'verified' }}</span> {{ $certification->category_tag }}</span>
@endif
@if($certification->is_featured)
<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary text-on-primary rounded font-label-sm text-xs font-semibold"><span class="material-symbols-outlined text-xs">stars</span> Unggulan</span>
@endif
</div>
@if($certification->validity_date)
<span class="text-xs px-2.5 py-1 rounded font-label-sm text-label-sm bg-surface-container-high text-primary font-medium">Valid s/d {{ $certification->validity_date->format('Y') }}</span>
@endif
</div>
<div class="flex items-start justify-between gap-4 mb-4">
<div>
<h3 class="font-headline-md text-title-md md:text-headline-md text-primary tracking-tight leading-snug">{{ $certification->product_name }}</h3>
<div class="font-label-md text-xs md:text-label-md text-on-surface-variant mt-1 leading-normal">{{ $certification->subtitle }}</div>
<div class="font-mono text-xs text-on-surface-variant mt-1">{{ $certification->model_spec_identifier }}</div>
</div>
<div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-primary-container text-inverse-primary flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-xl md:text-2xl">{{ $certification->card_icon ?: 'verified' }}</span></div>
</div>
@if(!empty($specRows))
<div class="space-y-2 py-3 md:py-4 my-2 md:my-3 border-t border-b border-outline-variant/30 text-xs md:text-body-sm font-body-sm">
@foreach($specRows as $row)
<div class="flex justify-between gap-4"><span class="text-on-surface-variant">{{ $row['label'] ?? '' }}:</span><span class="font-medium text-primary text-right">{{ $row['value'] ?? '' }}</span></div>
@endforeach
</div>
@endif
@if($certification->scope_description)
<p class="font-body-sm text-xs md:text-body-sm text-on-surface-variant leading-relaxed"><strong>Lingkup:</strong> {{ $certification->scope_description }}</p>
@endif
</div>
<div class="pt-4 md:pt-6 mt-3 md:mt-4 flex flex-col sm:flex-row items-center gap-2.5 md:gap-3">
<button class="cert-detail-btn w-full sm:flex-1 py-2 md:py-2.5 px-3 bg-primary text-on-primary rounded font-label-md text-xs md:text-label-md hover:bg-secondary transition flex items-center justify-center gap-2 shadow-sm" type="button"><span class="material-symbols-outlined text-sm">visibility</span> Show Gambar Sertifikasi</button>
@if($certification->certificate_pdf_url)
<a class="w-full sm:w-auto py-2 md:py-2.5 px-4 bg-surface-container-low text-primary rounded font-label-md text-xs md:text-label-md hover:bg-surface-container-high transition flex items-center justify-center gap-1.5" download href="{{ $certification->certificate_pdf_url }}"><span class="material-symbols-outlined text-sm">download</span> Download</a>
@endif
@if($certification->work)
<a class="w-full sm:w-auto py-2 md:py-2.5 px-4 bg-surface-container-low text-primary rounded font-label-md text-xs md:text-label-md hover:bg-surface-container-high transition flex items-center justify-center gap-1.5" href="{{ url('/works#'.$certification->work->work_slug) }}"><span class="material-symbols-outlined text-sm">arrow_outward</span> Lihat Produk</a>
@endif
</div>
</article>
@endforeach
</div><div class="flex flex-col sm:flex-row items-center justify-between gap-3 md:gap-4 mt-6 md:mt-8 p-3 md:px-6 bg-surface-container-lowest rounded-xl border border-outline-variant/30 shadow-sm relative z-10"><div class="flex flex-wrap items-center gap-2 text-xs md:text-body-sm font-body-sm text-on-surface-variant"><span class="inline-flex items-center px-2 py-0.5 rounded bg-surface-container text-xs font-medium text-secondary">Total 4 Dokumen Produk</span><span class="hidden sm:inline">Menampilkan <strong class="text-primary font-semibold">1 - 4</strong> dari <strong class="text-primary font-semibold">4</strong> Sertifikasi Produk</span><span class="sm:hidden">Menampilkan <strong class="text-primary font-semibold">2</strong> Produk Unggulan</span></div><nav aria-label="Navigasi Halaman Produk" class="flex items-center gap-1.5"><button class="inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg border border-outline-variant/30 text-on-surface-variant opacity-40 cursor-not-allowed transition" disabled="" title="Halaman Sebelumnya"><span class="material-symbols-outlined text-sm">chevron_left</span></button><button class="inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm font-semibold shadow-sm transition" aria-current="page">1</button><button class="inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg border border-outline-variant/30 text-on-surface-variant opacity-40 cursor-not-allowed transition" disabled="" title="Halaman Selanjutnya"><span class="material-symbols-outlined text-sm">chevron_right</span></button></nav></div></div></section>
<!-- SECTION 3: DOCUMENT REGISTRY & VERIFICATION TABLE -->

<!-- SECTION 4: ACCREDITATION BODIES & AUDIT PARTNERS -->

<!-- SECTION 5: AUDIT CONSULTATION & TENDER SUPPORT (NAVY INDUSTRIAL BLOCK) -->

<!-- MODAL: VERIFIKASI SERTIFIKAT INTERAKTIF -->
<div class="fixed inset-0 z-50 bg-primary/70 backdrop-blur-sm hidden items-center justify-center p-4" id="verificationModal">
<div class="bg-surface-container-lowest max-w-lg w-full rounded-xl shadow-2xl p-6 md:p-8 relative">
<button class="absolute top-4 right-4 text-outline hover:text-primary transition" onclick="closeVerificationModal()">
<span class="material-symbols-outlined">close</span>
</button>
<div class="flex items-center gap-3 mb-6">
<div class="w-12 h-12 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
<span class="material-symbols-outlined">verified</span>
</div>
<div>
<span class="font-label-caps text-label-caps text-secondary uppercase">Apex Verification Engine</span>
<h3 class="font-headline-sm text-headline-sm text-primary font-bold" id="modalCertTitle">Detail Lisensi</h3>
</div>
</div>
<div class="space-y-3 bg-surface-container-low p-4 rounded-lg font-body-sm text-body-sm">
<div class="flex justify-between border-b border-outline-variant/30 pb-2">
<span class="text-on-surface-variant">Nomor Registrasi:</span>
<span class="font-mono font-bold text-primary" id="modalCertId"></span>
</div>
<div class="flex justify-between border-b border-outline-variant/30 pb-2">
<span class="text-on-surface-variant">Badan Akreditasi:</span>
<span class="font-semibold text-primary" id="modalCertBody"></span>
</div>
<div class="flex justify-between border-b border-outline-variant/30 pb-2">
<span class="text-on-surface-variant">Validitas Hingga:</span>
<span class="font-medium text-primary" id="modalCertValidity"></span>
</div>
<div class="flex flex-col gap-1 pt-1">
<span class="text-on-surface-variant">Ruang Lingkup Sertifikasi:</span>
<span class="font-medium text-primary leading-normal" id="modalCertScope"></span>
</div>
</div>
<div class="hidden mt-4" id="modalCertImageWrap"><img alt="" class="w-full max-h-64 object-contain rounded-lg border border-outline-variant/30 bg-surface" id="modalCertImage"></div>
<div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
<a class="hidden w-full sm:flex-1 py-2.5 px-4 bg-primary text-on-primary hover:bg-secondary font-label-md text-label-md rounded font-semibold transition text-center items-center justify-center gap-2" download href="#" id="modalCertDownload"><span class="material-symbols-outlined text-sm">download</span> Download Sertifikat</a>
<button class="w-full sm:w-auto py-2.5 px-4 bg-surface-container hover:bg-surface-container-high text-primary font-label-md text-label-md rounded transition" onclick="closeVerificationModal()">
          Tutup
        </button>
</div>
</div>
</div>
<!-- SCRIPT UNTUK INTERAKTIVITAS CLIENT-SIDE -->
<script>
    const ROWS_PER_PAGE = 6;
    let activeCategory = 'all';
    let currentPage = 1;

    const rows = Array.from(document.querySelectorAll('.cert-row'));
    const emptyRow = document.getElementById('certEmptyRow');
    const searchInput = document.getElementById('certSearchInput');
    const pagination = document.getElementById('certPagination');
    const rangeInfo = document.getElementById('certRangeInfo');
    const totalInfo = document.getElementById('certTotal');

    const pageClasses = {
      active: 'inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm font-semibold shadow-sm transition',
      idle: 'inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container transition font-label-sm text-label-sm',
      arrow: 'inline-flex items-center justify-center w-8 h-8 md:w-9 md:h-9 rounded-lg border border-outline-variant/30 text-on-surface-variant transition disabled:opacity-40 disabled:cursor-not-allowed',
    };

    function setCertCategory(cat) {
      activeCategory = cat;
      currentPage = 1;
      document.querySelectorAll('.cert-category-filter').forEach((btn) => {
        btn.classList.remove('bg-primary', 'text-on-primary');
        btn.classList.add('bg-surface-container', 'text-on-surface-variant');
      });
      const activeBtn = document.getElementById('btn-cat-' + cat);
      if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-on-primary');
      }
      renderCertRows();
    }

    function handleCertFilter() {
      currentPage = 1;
      renderCertRows();
    }

    function goToCertPage(page) {
      currentPage = page;
      renderCertRows();
    }

    function arrowButton(icon, label, disabled, page) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = pageClasses.arrow;
      button.title = label;
      button.disabled = disabled;
      const glyph = document.createElement('span');
      glyph.className = 'material-symbols-outlined text-sm';
      glyph.textContent = icon;
      button.appendChild(glyph);
      button.addEventListener('click', () => goToCertPage(page));
      return button;
    }

    function renderCertRows() {
      const query = (searchInput?.value || '').toLowerCase().trim();
      const matched = rows.filter((row) => {
        const matchesCategory = activeCategory === 'all' || row.getAttribute('data-category') === activeCategory;
        return matchesCategory && (!query || (row.getAttribute('data-search') || '').includes(query));
      });

      const totalPages = Math.max(1, Math.ceil(matched.length / ROWS_PER_PAGE));
      currentPage = Math.min(Math.max(currentPage, 1), totalPages);
      const start = (currentPage - 1) * ROWS_PER_PAGE;
      const visible = new Set(matched.slice(start, start + ROWS_PER_PAGE));

      rows.forEach((row) => row.classList.toggle('hidden', !visible.has(row)));
      emptyRow?.classList.toggle('hidden', matched.length !== 0);

      if (totalInfo) totalInfo.textContent = String(matched.length);
      if (rangeInfo) {
        rangeInfo.textContent = matched.length === 0
          ? 'Tidak ada dokumen yang cocok'
          : `Menampilkan ${start + 1} - ${start + visible.size} dari ${matched.length} Dokumen Akreditasi`;
      }

      if (pagination) {
        const numbers = Array.from({ length: totalPages }, (_, i) => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = i + 1 === currentPage ? pageClasses.active : pageClasses.idle;
          button.textContent = String(i + 1);
          if (i + 1 === currentPage) button.setAttribute('aria-current', 'page');
          button.addEventListener('click', () => goToCertPage(i + 1));
          return button;
        });
        pagination.replaceChildren(
          arrowButton('chevron_left', 'Halaman Sebelumnya', currentPage === 1, currentPage - 1),
          ...numbers,
          arrowButton('chevron_right', 'Halaman Selanjutnya', currentPage === totalPages, currentPage + 1)
        );
      }
    }

    function openVerificationModal(source) {
      const data = source.dataset;
      document.getElementById('modalCertTitle').textContent = data.title || '';
      document.getElementById('modalCertId').textContent = data.reg || '-';
      document.getElementById('modalCertBody').textContent = data.body || '-';
      document.getElementById('modalCertValidity').textContent = data.validity || '-';
      document.getElementById('modalCertScope').textContent = data.scope || '-';

      const imageWrap = document.getElementById('modalCertImageWrap');
      const image = document.getElementById('modalCertImage');
      imageWrap.classList.toggle('hidden', !data.image);
      if (data.image) {
        image.src = data.image;
        image.alt = data.title || '';
      } else {
        image.removeAttribute('src');
      }

      const download = document.getElementById('modalCertDownload');
      download.classList.toggle('hidden', !data.pdf);
      if (data.pdf) download.href = data.pdf;

      const modal = document.getElementById('verificationModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeVerificationModal() {
      const modal = document.getElementById('verificationModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('.cert-detail-btn');
      if (trigger) openVerificationModal(trigger.closest('[data-title]'));
    });
    document.getElementById('verificationModal')?.addEventListener('click', (e) => {
      if (e.target.id === 'verificationModal') closeVerificationModal();
    });
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeVerificationModal();
    });

    renderCertRows();
  </script>
</div></main>
@include('layouts.footer')
<!-- Footer Penutup Sederhana -->
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash;
    if (hash) {
      const targetEl = document.querySelector(hash);
      if (targetEl) {
        setTimeout(() => {
          targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
          targetEl.classList.add('ring-4', 'ring-secondary');
          setTimeout(() => {
            targetEl.classList.remove('ring-4', 'ring-secondary');
          }, 3000);
        }, 300);
      }
    }
  });
</script>



























</body></html>