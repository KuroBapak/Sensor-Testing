<!DOCTYPE html><html lang="en" style=""><head><meta charset="utf-8"><meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>{{ $pageSettings?->seo_title ?? 'Daftar Produk & Hasil Pengerjaan (Works) | PT Apex Industrial Engineering' }}</title>
@if(!empty($pageSettings?->seo_description))
<meta name="description" content="{{ $pageSettings->seo_description }}">
@endif<meta content="web_standard" name="shell-type"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"><link href="https://fonts.googleapis.com" rel="preconnect"><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Montserrat:wght@600;700;800&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"><style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}</style><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">tailwind.config={"darkMode":"class","theme":{"extend":{ fontSize: {'lg': ['1rem', {lineHeight: '1.6'}], 'xl': ['1.125rem', {lineHeight: '1.5'}], '2xl': ['1.25rem', {lineHeight: '1.4'}], '3xl': ['1.5rem', {lineHeight: '1.3'}], '4xl': ['1.75rem', {lineHeight: '1.25'}], '5xl': ['2.125rem', {lineHeight: '1.15'}], '6xl': ['2.5rem', {lineHeight: '1.1'}]},"colors":{"on-background":"#0b1c30","on-primary-fixed-variant":"#364860","surface-container-high":"#dce9ff","on-primary-fixed":"#081c32","secondary":"#416279","on-primary-container":"#7689a4","on-secondary-fixed-variant":"#284a60","secondary-fixed":"#c8e6ff","on-primary":"#ffffff","surface-container":"#e5eeff","outline-variant":"#c4c6cd","on-secondary":"#ffffff","primary-fixed":"#d2e4ff","inverse-surface":"#213145","surface-tint":"#4d6079","on-surface-variant":"#44474d","on-secondary-container":"#45667d","error-container":"#ffdad6","surface":"#f8f9ff","error":"#ba1a1a","inverse-on-surface":"#eaf1ff","surface-container-highest":"#d3e4fe","surface-variant":"#d3e4fe","secondary-fixed-dim":"#a9cbe5","on-surface":"#0b1c30","on-tertiary-fixed":"#0d1c2d","surface-container-low":"#eff4ff","tertiary-fixed-dim":"#b9c8de","on-error":"#ffffff","on-tertiary-fixed-variant":"#39485a","tertiary-fixed":"#d4e4fa","surface-bright":"#f8f9ff","tertiary-container":"#122131","background":"#f8f9ff","on-secondary-fixed":"#001e2e","inverse-primary":"#b5c8e5","primary-fixed-dim":"#b5c8e5","outline":"#74777d","surface-container-lowest":"#ffffff","surface-dim":"#cbdbf5","secondary-container":"#c2e4ff","primary":"#000917","primary-container":"#0d2137","on-error-container":"#93000a","on-tertiary":"#ffffff","tertiary":"#000916","on-tertiary-container":"#7a899d"},"borderRadius":{"DEFAULT":"0.125rem","lg":"0.25rem","xl":"0.5rem","full":"0.75rem"},"spacing":{"margin":"1rem","space-md":"1rem","space-xs":"0.25rem","gutter-desktop":"1.5rem","space-2xl":"3rem","gutter":"1.5rem","margin-desktop":"3rem","space-lg":"1.5rem","margin-tablet":"2rem","space-3xl":"4rem","space-sm":"0.5rem","space-xl":"2rem"},"fontFamily":{"label-sm":["Inter"],"display-hero-mobile":["Montserrat"],"body-md":["Inter"],"headline-lg-mobile":["Montserrat"],"headline-lg":["Montserrat"],"label-caps":["Montserrat"],"display-hero":["Montserrat"],"body-lg":["Inter"],"headline-sm":["Montserrat"],"body-sm":["Inter"],"label-md":["Inter"],"headline-md":["Montserrat"],"title-md":["Inter"]},"fontSize":{"label-sm":["12px",{"lineHeight":"16px","fontWeight":"500"}],"display-hero-mobile":["28px",{"lineHeight":"36px","letterSpacing":"-0.02em","fontWeight":"800"}],"body-md":["15px",{"lineHeight":"24px","fontWeight":"400"}],"headline-lg-mobile":["24px",{"lineHeight":"32px","letterSpacing":"-0.01em","fontWeight":"700"}],"headline-lg":["30px",{"lineHeight":"38px","letterSpacing":"-0.015em","fontWeight":"700"}],"label-caps":["12px",{"lineHeight":"16px","letterSpacing":"0.08em","fontWeight":"700"}],"display-hero":["40px",{"lineHeight":"48px","letterSpacing":"-0.02em","fontWeight":"800"}],"body-lg":["16px",{"lineHeight":"26px","fontWeight":"400"}],"headline-sm":["18px",{"lineHeight":"26px","fontWeight":"600"}],"body-sm":["14px",{"lineHeight":"20px","fontWeight":"400"}],"label-md":["14px",{"lineHeight":"20px","fontWeight":"500"}],"headline-md":["22px",{"lineHeight":"30px","letterSpacing":"-0.01em","fontWeight":"700"}],"title-md":["16px",{"lineHeight":"24px","fontWeight":"600"}]}}}};</script>@include('layouts.scale')
</head><body class="bg-surface font-body-md text-on-surface antialiased selection:bg-secondary-container selection:text-on-secondary-container min-h-screen overflow-y-auto">
@include('layouts.navbar')
<main class="w-full bg-surface min-h-screen pb-space-lg"><div class="flex flex-col w-full">
<!-- Top Breadcrumb & Hero Archive Section -->
<section class="w-full bg-surface-container-low border-b border-surface-container-high/60 py-space-xl lg:py-space-2xl">
<div class="max-w-[1360px] mx-auto px-margin md:px-margin-tablet lg:px-margin-desktop flex flex-col">
<!-- Breadcrumb -->
<nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6" aria-label="Breadcrumb">
  <a href="{{ url('/') }}" class="flex items-center gap-1.5 hover:text-blue-600 transition-colors" data-path="landing-page">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
    <span class="">Beranda</span>
  </a>
  <span class="text-slate-400">/</span>
  <span class="text-slate-900 font-semibold">Works</span>
</nav>
<!-- Badge & Title -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-space-lg">
<div class="">
<h1 class="font-headline-lg text-[24px] sm:text-headline-lg text-primary uppercase tracking-tight leading-tight">{{ $pageSettings?->hero_title ?? 'DAFTAR PRODUK & HASIL PENGERJAAN (WORKS)' }}</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant mt-space-sm leading-relaxed" data-stitch-orig-opacity="0">{{ $pageSettings?->hero_subtitle ?? 'Katalog produk rekayasa presisi dan hasil pengerjaan manufaktur permesinan CNC 5-sumbu, fabrikasi bejana tekan dan pengelasan berstandar ASME, serta sistem perakitan mekatronika industri dari PT Apex Industrial Engineering.' }}</p>
</div>
</div>
<!-- Quick Metrics Strip -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-space-md mt-space-md md:mt-space-xl pt-space-md md:pt-space-lg border-t border-surface-container-high/80">
@if(!empty($pageSettings?->stats))
  @foreach($pageSettings->stats as $stat)
  <div class="flex flex-col p-3 md:p-space-md bg-surface-container-lowest rounded shadow-sm">
    <div class="flex items-center justify-between text-secondary mb-0.5 md:mb-1">
      <span class="font-label-caps text-[10px] md:text-label-caps uppercase">{{ $stat['label'] ?? '' }}</span>
      <span class="material-symbols-outlined text-base md:text-lg">{{ $stat['icon'] ?? 'deployed_code' }}</span>
    </div>
    <span class="font-headline-sm md:font-headline-md text-headline-sm md:text-headline-md text-primary font-bold">{{ $stat['value'] ?? '' }}</span>
    <span class="font-body-sm text-[11px] md:text-body-sm text-on-surface-variant mt-0.5">{{ $stat['caption'] ?? '' }}</span>
  </div>
  @endforeach
@else
<div class="flex flex-col p-3 md:p-space-md bg-surface-container-lowest rounded shadow-sm"><div class="flex items-center justify-between text-secondary mb-0.5 md:mb-1"><span class="font-label-caps text-[10px] md:text-label-caps uppercase">Total Deliveries</span><span class="material-symbols-outlined text-base md:text-lg">deployed_code</span></div><span class="font-headline-sm md:font-headline-md text-headline-sm md:text-headline-md text-primary font-bold">120+</span><span class="font-body-sm text-[11px] md:text-body-sm text-on-surface-variant mt-0.5">Proyek Industri</span></div><div class="flex flex-col p-3 md:p-space-md bg-surface-container-lowest rounded shadow-sm"><div class="flex items-center justify-between text-secondary mb-0.5 md:mb-1"><span class="font-label-caps text-[10px] md:text-label-caps uppercase">Micron Gauge</span><span class="material-symbols-outlined text-base md:text-lg">straighten</span></div><span class="font-headline-sm md:font-headline-md text-headline-sm md:text-headline-md text-primary font-bold">±0.002mm</span><span class="font-body-sm text-[11px] md:text-body-sm text-on-surface-variant mt-0.5">Presisi 5-Axis</span></div><div class="flex flex-col p-3 md:p-space-md bg-surface-container-lowest rounded shadow-sm"><div class="flex items-center justify-between text-secondary mb-0.5 md:mb-1"><span class="font-label-caps text-[10px] md:text-label-caps uppercase">Quality Assurance</span><span class="material-symbols-outlined text-base md:text-lg">flaky</span></div><span class="font-headline-sm md:font-headline-md text-headline-sm md:text-headline-md text-primary font-bold">100%</span><span class="font-body-sm text-[11px] md:text-body-sm text-on-surface-variant mt-0.5">NDT &amp; Hydro Proof</span></div><div class="flex flex-col p-3 md:p-space-md bg-surface-container-lowest rounded shadow-sm"><div class="flex items-center justify-between text-secondary mb-0.5 md:mb-1"><span class="font-label-caps text-[10px] md:text-label-caps uppercase">Compliance</span><span class="material-symbols-outlined text-base md:text-lg">verified_user</span></div><span class="font-headline-sm md:font-headline-md text-headline-sm md:text-headline-md text-primary font-bold">AS9100D</span><span class="font-body-sm text-[11px] md:text-body-sm text-on-surface-variant mt-0.5">ASME &amp; ISO 9001</span></div>
@endif
</div>
</div>
</section>
<!-- Interactive Search, Filter Bar & Toolbar -->
<section class="w-full bg-surface py-3 md:py-space-lg md:sticky md:top-20 z-30 shadow-[0_4px_12px_rgba(0,0,0,0.03)] backdrop-blur-md bg-surface/95">
<div class="max-w-[1360px] mx-auto px-margin md:px-margin-tablet lg:px-margin-desktop">
<div class="flex flex-col gap-space-md">
<!-- Top Row: Horizontal Carousel Filter Tabs with Arrow Buttons -->
<div class="relative flex items-center w-full">
<button aria-label="Scroll Tabs Kiri" class="shrink-0 w-9 h-9 mr-2 flex items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors shadow-sm focus:outline-none" id="tabScrollLeftBtn" type="button">
<span class="material-symbols-outlined text-lg">chevron_left</span>
</button>
<!-- Carousel Scrollable Container -->
<div class="flex items-center gap-space-xs overflow-x-auto scroll-smooth py-1 scrollbar-none flex-1" id="filterTabGroup" style="-ms-overflow-style:none; scrollbar-width:none;">
<button class="filter-tab-btn active-tab shrink-0 inline-flex items-center gap-space-xs px-3.5 py-2 rounded font-label-md text-xs md:text-label-md bg-primary-container text-on-primary whitespace-nowrap transition-all shadow-sm" data-filter="all" data-label="Semua Layanan (ALL WORKS)">
<span class="">Semua Layanan (ALL WORKS)</span>
<span class="tab-count px-1.5 py-0.5 text-[11px] rounded bg-on-primary/20 text-on-primary font-bold">{{ $works->count() }}</span>
</button>
@foreach($services as $service)
<button class="filter-tab-btn shrink-0 inline-flex items-center gap-space-xs px-3.5 py-2 rounded font-label-md text-xs md:text-label-md bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high whitespace-nowrap transition-all" data-filter="{{ $service->slug }}" data-label="{{ $service->service_title }}">
<span class="material-symbols-outlined text-sm">{{ $service->service_icon ?: 'category' }}</span>
<span class="">{{ $service->service_title }}</span>
</button>
@endforeach
</div>
<button aria-label="Scroll Tabs Kanan" class="shrink-0 w-9 h-9 ml-2 flex items-center justify-center rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors shadow-sm focus:outline-none" id="tabScrollRightBtn" type="button">
<span class="material-symbols-outlined text-lg">chevron_right</span>
</button>
</div>
<!-- Bottom Row: Search Bar & Sorting Dropdown -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-space-sm pt-1">
<div class="relative w-full sm:flex-1">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg pointer-events-none">search</span>
<input class="w-full pl-10 pr-space-md py-2 text-body-sm font-body-sm bg-surface-container-lowest text-on-surface rounded shadow-sm focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-outline-variant border border-surface-container-high/60" id="projectSearchInput" placeholder="Cari nama komponen, layanan, material (Ti-6Al-4V, 7075-T6), PLC, spek..." type="text">
</div>
<div class="relative w-full sm:w-auto min-w-[220px]">
<select class="w-full px-space-md py-2 text-label-sm font-label-sm bg-surface-container-lowest text-on-surface rounded shadow-sm focus:outline-none focus:ring-1 focus:ring-primary appearance-none pr-8 border border-surface-container-high/60 cursor-pointer" id="projectSortSelect">
<option selected="" value="newest">Terbaru / Newest First</option>
<option value="tolerance">Toleransi Tertinggi (Micron)</option>
<option value="scale">Skala Batch Terbesar</option>
</select>
<span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-outline text-base pointer-events-none">expand_more</span>
</div>
</div>
</div>
</div>
</section>
<!-- Project Grid Section -->
<section class="w-full bg-surface pt-space-xl pb-space-md">
<div class="max-w-[1360px] mx-auto px-margin md:px-margin-tablet lg:px-margin-desktop">
<!-- Active Results Overview -->
<div class="flex items-center justify-between pb-space-md mb-space-lg border-b border-surface-container-high/60">
<div class="flex items-center gap-space-xs font-label-sm text-label-sm text-secondary">
<span class="material-symbols-outlined text-base">tune</span>
<span class="" id="activeFilterLabel">Kategori Aktif: Semua Layanan (ALL WORKS)</span>
<span class="text-outline">/</span>
<span class="font-semibold text-primary" id="resultsCountNotice">{{ $works->count() }} Proyek Aktif Terdaftar</span>
</div>
<div class="hidden sm:flex items-center gap-1 text-label-sm font-label-sm text-outline">
<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
<span class="">Sertifikasi AS9100D &amp; Rekam NDT Lengkap</span>
</div>
</div>
<!-- Projects Grid (3 columns on desktop) -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-gutter-desktop sm:grid-cols-2" id="projectCatalogGrid">
@forelse($works as $work)
@php
  $categoryLabel = $work->service?->service_title ?? 'Engineering Works';
  $images = $work->gallery_urls;
  $tags = array_slice(array_values(array_filter($work->highlight_tags ?? [])), 0, 3);
  $standards = array_values(array_filter($work->quality_standards ?? []));
  $searchBlob = \Illuminate\Support\Str::lower(implode(' ', array_filter([
    $work->material, $work->tolerance_specs, $work->batch_scale, $work->serial_number,
    $work->client_sector, $work->top_badge, ...$tags, ...$standards,
  ])));
@endphp
<article class="project-card group flex flex-col bg-surface-container-lowest rounded overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer scroll-mt-48" data-slug="{{ $work->work_slug }}" data-title="{{ $work->work_title }}" data-category="{{ $work->service?->slug }}" data-category-label="{{ $categoryLabel }}" data-serial="{{ $work->serial_number }}" data-sector="{{ $work->client_sector }}" data-desc="{{ $work->description_plain }}" data-material="{{ $work->material }}" data-tolerance="{{ $work->tolerance_specs }}" data-tolerance-value="{{ $work->tolerance_value }}" data-batch="{{ $work->batch_scale }}" data-scale="{{ $work->scale_value }}" data-date="{{ $work->production_date?->toDateString() }}" data-date-label="{{ $work->production_date_label }}" data-gallery="{{ json_encode($images) }}" data-standards="{{ json_encode($standards) }}" data-specs="{{ $searchBlob }}">
<div class="relative h-24 sm:h-40 md:h-52 w-full overflow-hidden bg-surface-container-high">
@if(!empty($images))
<img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $work->work_title }}" loading="lazy" src="{{ $images[0] }}">
@else
<div class="w-full h-full flex items-center justify-center text-outline-variant"><span class="material-symbols-outlined text-5xl">precision_manufacturing</span></div>
@endif
@if($work->top_badge)
<div class="absolute top-2 left-2 flex items-center gap-1 px-1.5 py-0.5 md:px-2.5 md:py-1 bg-primary text-on-primary rounded font-label-caps text-[9px] md:text-label-caps tracking-wider shadow"><span class="w-1.5 h-1.5 rounded-full bg-primary-fixed"></span><span class="">{{ $work->top_badge }}</span></div>
@endif
@if($work->bottom_badge)
<div class="absolute bottom-2 right-2 px-1.5 py-0.5 bg-surface/90 backdrop-blur-sm text-on-surface rounded font-label-caps text-[9px] md:text-[11px] font-semibold">{{ $work->bottom_badge }}</div>
@endif
</div>
<div class="p-2 md:p-space-md flex-1 flex flex-col justify-between">
<div>
<div class="flex items-center justify-between text-secondary font-label-caps text-[10px] md:text-label-caps mb-0.5 md:mb-1"><span class="">{{ $work->serial_number }}</span><span class="hidden sm:inline">{{ $work->client_sector }}</span></div>
<h2 class="font-headline-sm text-xs sm:text-base md:text-headline-sm text-primary uppercase font-bold group-hover:text-secondary transition-colors line-clamp-1 sm:line-clamp-none">{{ $work->work_title }}</h2>
<p class="hidden sm:block font-body-sm text-body-sm text-on-surface-variant mt-space-xs line-clamp-2 leading-relaxed">{{ $work->short_description }}</p>
</div>
<div class="mt-2 md:mt-space-md pt-2 md:pt-space-md border-t border-surface-container-low">
<div class="flex flex-wrap gap-1 mb-1.5 md:mb-space-md">
@foreach($tags as $tag)
<span class="{{ $loop->index >= 2 ? 'hidden sm:inline-block' : '' }} px-1.5 py-0.5 {{ $loop->first ? 'bg-surface-container-low text-secondary' : 'bg-primary-container/10 text-primary-container font-medium' }} font-label-sm text-[10px] md:text-[12px] rounded">{{ $tag }}</span>
@endforeach
</div>
<button class="open-modal-trigger w-full inline-flex items-center justify-center gap-1 px-2 py-1.5 md:py-2.5 bg-surface-container text-on-surface hover:bg-primary hover:text-on-primary font-label-md text-[11px] md:text-label-md rounded transition-all" type="button"><span class="">Detail</span><span class="material-symbols-outlined text-sm md:text-base">arrow_forward</span></button>
</div>
</div>
</article>
@empty
@endforelse
</div>
<!-- Empty Filter State Notice (Hidden by default) -->
<div class="hidden flex-col items-center justify-center py-space-3xl text-center bg-surface-container-low rounded border border-surface-container-high/60 my-space-lg" id="noResultsState">
<span class="material-symbols-outlined text-5xl text-outline mb-space-sm">filter_alt_off</span>
<h3 class="font-headline-sm text-headline-sm text-primary uppercase">Tidak ada proyek yang sesuai</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-md mt-1">
          Kombinasi kata kunci pencarian atau filter kategori layanan tidak menghasilkan portofolio aktif. Coba gunakan istilah umum seperti "chucking", "panel", "shaft", atau pilih kategori lainnya.
        </p>
<button class="mt-space-md px-space-md py-2 bg-primary text-on-primary font-label-md text-label-md rounded shadow-sm hover:bg-primary-container transition-all" onclick="resetFilters()">
          Reset Filter &amp; Pencarian
        </button>
</div>
<!-- Interactive Pagination & Display Configuration -->
<div class="mt-space-2xl pt-space-lg border-t border-surface-container-high/60 flex flex-col md:flex-row items-center justify-between gap-space-md pb-6">
<div class="flex items-center gap-space-sm text-secondary font-body-sm text-body-sm">
<span class="" id="paginationInfo">Menampilkan 1 - {{ min($works->count(), 9) }} dari {{ $works->count() }} Total Hasil Proyek</span>
<span class="hidden sm:inline text-outline-variant">|</span>
<div class="hidden sm:flex items-center gap-1">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
<span class="font-label-sm text-label-sm text-on-surface-variant">Arsip Batch 2024-2025</span>
</div>
</div>
<div class="flex items-center gap-space-sm">
<!-- Page Number Buttons -->
<div class="flex items-center gap-1" id="paginationButtonList">
<button aria-label="Previous Page" class="w-9 h-9 flex items-center justify-center rounded bg-surface-container text-on-surface hover:bg-surface-container-high disabled:opacity-40 disabled:pointer-events-none transition-colors" disabled="" id="prevPageBtn" onclick="changePage('prev')" type="button">
<span class="material-symbols-outlined text-base">chevron_left</span>
</button>
<div class="flex items-center gap-1" id="pageNumberList"></div>
<button aria-label="Next Page" class="w-9 h-9 flex items-center justify-center rounded bg-surface-container text-on-surface hover:bg-surface-container-high disabled:opacity-40 disabled:pointer-events-none transition-colors" id="nextPageBtn" onclick="changePage('next')" type="button">
<span class="material-symbols-outlined text-base">chevron_right</span>
</button>
</div>
<!-- Items Per Page Dropdown -->
<div class="relative ml-space-sm">
<select class="pl-space-sm pr-7 py-1.5 text-label-sm font-label-sm bg-surface-container text-on-surface rounded focus:outline-none appearance-none" id="itemsPerPageSelect">
<option value="9">9 per halaman</option>
<option value="18">18 per halaman</option>
<option value="all">Semua</option>
</select>
<span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-outline text-sm pointer-events-none">expand_more</span>
</div>
</div>
</div>
</div>
</section>
<!-- Interactive Showcase Modal (Large Image & Full Comprehensive Details - 2 Columns) -->
<div aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 md:p-6 bg-black/80 backdrop-blur-md transition-all duration-300 opacity-0 pointer-events-none" id="projectDetailModal" role="dialog">
<div class="relative w-full max-w-5xl bg-surface-container-lowest rounded-lg sm:rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col border border-surface-container-high transform transition-all duration-300 scale-95" id="modalContentContainer">
<!-- Modal Floating / Sticky Close Button -->
<button aria-label="Tutup Showcase Modal" class="absolute top-3.5 right-3.5 z-20 w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center bg-surface-container-lowest/90 text-on-surface hover:bg-primary hover:text-on-primary shadow-md border border-surface-container-high transition-all focus:outline-none" onclick="closeProjectModal()" type="button">
<span class="material-symbols-outlined text-xl sm:text-2xl font-bold leading-none">close</span>
</button>
<!-- Modal Scrollable Content (2-Column Responsive Layout) -->
<div class="overflow-y-auto flex-1">
<div class="grid grid-cols-1 lg:grid-cols-12 min-h-full">
<!-- Kolom Kiri: Showcase Gambar Besar Beresolusi Tinggi -->
<div class="lg:col-span-5 bg-surface-container-low flex flex-col justify-between p-space-md sm:p-space-lg border-b lg:border-b-0 lg:border-r border-surface-container-high/80"><div class="flex flex-col gap-3 h-full justify-between"><div class="flex flex-col gap-3"><div class="relative w-full aspect-[4/3] overflow-hidden rounded-xl bg-surface-container-high shadow-md group border border-surface-container-high/80"><img alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" id="modalShowcaseImg"><div class="hidden w-full h-full flex items-center justify-center text-outline-variant" id="modalImagePlaceholder"><span class="material-symbols-outlined text-6xl">precision_manufacturing</span></div><div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start"><span class="px-2.5 py-1 bg-primary/95 text-on-primary font-label-caps text-[10px] rounded uppercase shadow backdrop-blur-xs font-semibold flex items-center gap-1.5" id="modalOverlaySerial"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>SAMPLE PRODUCT OUTPUT</span></div></div><div class="grid grid-cols-3 gap-3" id="modalGalleryThumbs"></div><p class="text-xs text-on-surface-variant font-medium mt-3 leading-relaxed text-center lg:text-left bg-surface-container-lowest p-3 rounded border border-surface-container-high"><span class="font-semibold text-primary block mb-0.5 font-mono">DOKUMENTASI BENCHMARK:</span> Gambar di atas merupakan representasi sampel output produk manufaktur presisi dan hasil pengerjaan fabrikasi berstandar industri.</p></div></div></div>
<!-- Kolom Kanan: Detail Teks Lengkap, Spesifikasi & Prosedur QA -->
<div class="lg:col-span-7 p-space-lg sm:p-space-xl flex flex-col justify-between space-y-space-md bg-surface-container-lowest"><div class="space-y-space-md"><div class="pr-8"><div class="flex flex-wrap items-center gap-2 mb-2"><span class="px-2.5 py-0.5 bg-primary text-on-primary font-label-caps text-[10px] rounded uppercase font-semibold" id="modalCategoryTag"></span><span class="text-xs text-outline font-mono" id="modalSerialTag"></span></div><h2 class="font-headline-sm sm:font-headline-md text-headline-sm sm:text-headline-md text-primary uppercase font-bold tracking-tight leading-tight" id="modalTitle"></h2></div><p class="font-body-md text-body-sm sm:text-body-md text-on-surface-variant leading-relaxed" id="modalDescription"></p><div class="pt-2 border-t border-surface-container-high/60"><h4 class="font-label-caps text-label-caps text-secondary uppercase mb-2 flex items-center gap-1"><span class="material-symbols-outlined text-sm">tune</span>Spesifikasi Utama</h4><div class="flex flex-wrap gap-1.5" id="modalSpecsList"><span class="px-2.5 py-1 bg-surface-container text-on-surface rounded font-label-sm text-xs" id="modalMaterial"></span><span class="px-2.5 py-1 bg-surface-container text-on-surface rounded font-label-sm text-xs" id="modalTolerance"></span><span class="px-2.5 py-1 bg-surface-container text-on-surface rounded font-label-sm text-xs" id="modalBatch"></span></div></div><div class="p-3 bg-surface-container-low rounded border border-surface-container-high/60 flex items-center justify-between" id="modalDeliveryDateWrap"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-base text-secondary">calendar_today</span><div><span class="font-label-caps text-[10px] text-secondary uppercase block">Tanggal Produksi / Delivery</span><span class="font-label-md text-xs sm:text-body-sm text-primary font-semibold" id="modalDeliveryDate"></span></div></div></div><div class="pt-1"><h4 class="font-label-caps text-label-caps text-secondary uppercase mb-2 flex items-center gap-1"><span class="material-symbols-outlined text-sm">verified</span>Sertifikasi &amp; Standar Mutu</h4><div class="flex flex-wrap gap-1.5" id="modalStandardsTags"></div></div></div><div class="pt-space-md border-t border-surface-container flex items-center justify-end"><button class="w-full sm:w-auto px-space-xl py-2 bg-primary text-on-primary hover:bg-primary-container font-label-md text-label-md rounded transition-colors text-center shadow-sm" onclick="closeProjectModal()" type="button">Tutup Detail</button></div></div>
</div>
</div>
</div>
</div>
<!-- Interactive Logic Script -->
<script>
    // State for filter, search, sort, and pagination (all handled in the browser)
    let currentCategory = 'all';
    let searchQuery = '';
    let sortMode = 'newest';
    let currentPage = 1;
    let perPage = 9;

    const grid = document.getElementById('projectCatalogGrid');
    const cards = Array.from(document.querySelectorAll('.project-card'));
    const cardOrder = new Map(cards.map((card, index) => [card, index]));
    const filterButtons = Array.from(document.querySelectorAll('.filter-tab-btn'));
    const searchInput = document.getElementById('projectSearchInput');
    const sortSelect = document.getElementById('projectSortSelect');
    const perPageSelect = document.getElementById('itemsPerPageSelect');
    const noResults = document.getElementById('noResultsState');
    const activeLabel = document.getElementById('activeFilterLabel');
    const countNotice = document.getElementById('resultsCountNotice');
    const paginationInfo = document.getElementById('paginationInfo');
    const pageNumberList = document.getElementById('pageNumberList');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const tabScrollContainer = document.getElementById('filterTabGroup');
    const activePageClasses = 'page-num-btn w-9 h-9 flex items-center justify-center rounded font-label-md text-label-md bg-primary text-on-primary shadow-sm transition-colors';
    const idlePageClasses = 'page-num-btn w-9 h-9 flex items-center justify-center rounded font-label-md text-label-md bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors';

    document.getElementById('tabScrollLeftBtn')?.addEventListener('click', () => {
      tabScrollContainer?.scrollBy({ left: -260, behavior: 'smooth' });
    });
    document.getElementById('tabScrollRightBtn')?.addEventListener('click', () => {
      tabScrollContainer?.scrollBy({ left: 260, behavior: 'smooth' });
    });

    function numericAttr(card, name) {
      const raw = card.getAttribute(name);
      if (raw === null || raw === '') return null;
      const value = parseFloat(raw);
      return Number.isNaN(value) ? null : value;
    }

    // Numeric comparison where records without a value always sort last.
    function compareNullsLast(a, b, direction) {
      if (a === null && b === null) return 0;
      if (a === null) return 1;
      if (b === null) return -1;
      return direction === 'asc' ? a - b : b - a;
    }

    function compareCards(a, b) {
      let result = 0;
      if (sortMode === 'scale') {
        result = compareNullsLast(numericAttr(a, 'data-scale'), numericAttr(b, 'data-scale'), 'desc');
      } else if (sortMode === 'tolerance') {
        result = compareNullsLast(numericAttr(a, 'data-tolerance-value'), numericAttr(b, 'data-tolerance-value'), 'asc');
      } else {
        const dateA = a.getAttribute('data-date') || '';
        const dateB = b.getAttribute('data-date') || '';
        if (dateA !== dateB) result = dateA === '' ? 1 : dateB === '' ? -1 : (dateA < dateB ? 1 : -1);
      }
      return result !== 0 ? result : cardOrder.get(a) - cardOrder.get(b);
    }

    function matchesFilters(card) {
      const category = card.getAttribute('data-category') || '';
      const matchesCategory = currentCategory === 'all' || category === currentCategory;
      if (!matchesCategory) return false;
      if (!searchQuery) return true;
      const haystack = [
        card.getAttribute('data-title'),
        card.getAttribute('data-desc'),
        card.getAttribute('data-specs'),
        card.getAttribute('data-category-label'),
        category,
      ].join(' ').toLowerCase();
      return haystack.includes(searchQuery);
    }

    function pageButton(page) {
      const button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('data-page', String(page));
      button.className = page === currentPage ? activePageClasses : idlePageClasses;
      button.textContent = String(page);
      button.addEventListener('click', () => goToPage(page));
      return button;
    }

    function render() {
      const matched = cards.filter(matchesFilters).sort(compareCards);
      const total = matched.length;
      const size = perPage === 'all' ? Math.max(total, 1) : perPage;
      const totalPages = Math.max(1, Math.ceil(total / size));
      currentPage = Math.min(Math.max(currentPage, 1), totalPages);
      const start = (currentPage - 1) * size;
      const visible = new Set(matched.slice(start, start + size));

      matched.forEach((card) => grid.appendChild(card));
      cards.forEach((card) => card.classList.toggle('hidden', !visible.has(card)));

      noResults.classList.toggle('hidden', total !== 0);
      noResults.classList.toggle('flex', total === 0);

      if (countNotice) countNotice.textContent = `${total} Proyek Aktif Terdaftar`;
      if (paginationInfo) {
        paginationInfo.textContent = total === 0
          ? 'Tidak ada hasil proyek'
          : `Menampilkan ${start + 1} - ${start + visible.size} dari ${total} Total Hasil Proyek`;
      }

      if (pageNumberList) {
        pageNumberList.replaceChildren(...Array.from({ length: totalPages }, (_, i) => pageButton(i + 1)));
      }
      if (prevPageBtn) prevPageBtn.disabled = currentPage === 1;
      if (nextPageBtn) nextPageBtn.disabled = currentPage === totalPages;
    }

    function goToPage(page) {
      currentPage = page;
      render();
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function changePage(direction) {
      goToPage(direction === 'prev' ? currentPage - 1 : currentPage + 1);
    }

    function activateTab(button) {
      filterButtons.forEach((b) => {
        b.classList.remove('bg-primary-container', 'text-on-primary', 'shadow-sm', 'active-tab');
        b.classList.add('bg-surface-container', 'text-on-surface-variant');
      });
      button.classList.add('bg-primary-container', 'text-on-primary', 'shadow-sm', 'active-tab');
      button.classList.remove('bg-surface-container', 'text-on-surface-variant');

      currentCategory = button.getAttribute('data-filter') || 'all';
      currentPage = 1;
      if (activeLabel) activeLabel.textContent = `Kategori Aktif: ${button.getAttribute('data-label') || ''}`;
      render();
    }

    filterButtons.forEach((button) => button.addEventListener('click', () => activateTab(button)));

    searchInput?.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim().toLowerCase();
      currentPage = 1;
      render();
    });

    sortSelect?.addEventListener('change', (e) => {
      sortMode = e.target.value;
      currentPage = 1;
      render();
    });

    perPageSelect?.addEventListener('change', (e) => {
      perPage = e.target.value === 'all' ? 'all' : parseInt(e.target.value, 10);
      currentPage = 1;
      render();
    });

    function resetFilters() {
      if (searchInput) searchInput.value = '';
      searchQuery = '';
      if (sortSelect) sortSelect.value = 'newest';
      sortMode = 'newest';
      if (filterButtons.length > 0) activateTab(filterButtons[0]);
    }

    // Modal
    const modal = document.getElementById('projectDetailModal');
    const modalContainer = document.getElementById('modalContentContainer');
    const modalImage = document.getElementById('modalShowcaseImg');
    const modalImagePlaceholder = document.getElementById('modalImagePlaceholder');
    const modalThumbs = document.getElementById('modalGalleryThumbs');

    function setText(id, value) {
      const element = document.getElementById(id);
      if (element) element.textContent = value;
    }

    function parseList(raw) {
      try {
        const parsed = JSON.parse(raw || '[]');
        return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
      } catch (e) {
        return [];
      }
    }

    function showModalImage(url, alt) {
      const hasImage = Boolean(url);
      modalImage.classList.toggle('hidden', !hasImage);
      modalImagePlaceholder.classList.toggle('hidden', hasImage);
      if (hasImage) {
        modalImage.src = url;
        modalImage.alt = alt;
      } else {
        modalImage.removeAttribute('src');
      }
    }

    function renderGallery(images, alt) {
      modalThumbs.replaceChildren();
      showModalImage(images[0] || '', alt);
      if (images.length < 2) return;

      images.forEach((url, index) => {
        const thumb = document.createElement('button');
        thumb.type = 'button';
        thumb.className = 'gallery-thumb-item aspect-[4/3] rounded-lg overflow-hidden border border-surface-container-high cursor-pointer hover:border-primary transition-all duration-300 shadow-sm';
        const img = document.createElement('img');
        img.src = url;
        img.alt = `${alt} ${index + 1}`;
        img.className = 'w-full h-full object-cover';
        thumb.appendChild(img);
        thumb.addEventListener('click', () => showModalImage(url, alt));
        modalThumbs.appendChild(thumb);
      });
    }

    function renderStandards(standards) {
      const wrapper = document.getElementById('modalStandardsTags');
      wrapper.replaceChildren(...standards.map((standard, index) => {
        const chip = document.createElement('span');
        chip.className = index === 0
          ? 'inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded font-label-sm text-xs font-semibold'
          : 'inline-flex items-center gap-1 px-2.5 py-1 bg-surface-container text-secondary rounded font-label-sm text-xs';
        chip.textContent = standard;
        return chip;
      }));
    }

    function populateAndOpenModalFromCard(card) {
      if (!modal) return;
      const title = card.getAttribute('data-title') || '';
      const serial = card.getAttribute('data-serial') || '';
      const dateLabel = card.getAttribute('data-date-label') || '';

      renderGallery(parseList(card.getAttribute('data-gallery')), title);
      setText('modalOverlaySerial', serial);
      setText('modalTitle', title);
      setText('modalCategoryTag', card.getAttribute('data-category-label') || '');
      setText('modalSerialTag', serial ? `#${serial}` : '');
      setText('modalDescription', card.getAttribute('data-desc') || '');
      setText('modalMaterial', card.getAttribute('data-material') || '');
      setText('modalTolerance', card.getAttribute('data-tolerance') || '');
      setText('modalBatch', card.getAttribute('data-batch') || '');
      setText('modalDeliveryDate', dateLabel);
      document.getElementById('modalDeliveryDateWrap')?.classList.toggle('hidden', dateLabel === '');
      renderStandards(parseList(card.getAttribute('data-standards')));

      ['modalMaterial', 'modalTolerance', 'modalBatch'].forEach((id) => {
        const element = document.getElementById(id);
        element.classList.toggle('hidden', element.textContent === '');
      });

      modal.classList.remove('opacity-0', 'pointer-events-none');
      modal.classList.add('opacity-100', 'pointer-events-auto');
      modalContainer?.classList.remove('scale-95');
      modalContainer?.classList.add('scale-100');
      document.body.style.overflow = 'hidden';
    }

    function closeProjectModal() {
      if (!modal) return;
      modal.classList.remove('opacity-100', 'pointer-events-auto');
      modal.classList.add('opacity-0', 'pointer-events-none');
      modalContainer?.classList.remove('scale-100');
      modalContainer?.classList.add('scale-95');
      document.body.style.overflow = '';
    }

    cards.forEach((card) => {
      card.addEventListener('click', (e) => {
        if (e.target.closest('a[href]')) return;
        populateAndOpenModalFromCard(card);
      });
      card.querySelector('.open-modal-trigger')?.addEventListener('click', (e) => {
        e.stopPropagation();
        populateAndOpenModalFromCard(card);
      });
    });

    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeProjectModal();
    });
    modal?.addEventListener('click', (e) => {
      if (e.target === modal) closeProjectModal();
    });

    // Initial state: ?category={service slug} selects a tab, #{work slug} opens that work's modal.
    const categoryParam = new URLSearchParams(window.location.search).get('category');
    const categoryButton = categoryParam
      ? filterButtons.find((button) => button.getAttribute('data-filter') === categoryParam)
      : null;

    if (categoryButton) {
      activateTab(categoryButton);
      tabScrollContainer?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      render();
    }

    const hash = decodeURIComponent(window.location.hash.slice(1));
    if (hash) {
      const slugify = (value) => value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
      const targetCard = cards.find((card) => card.getAttribute('data-slug') === hash)
        || cards.find((card) => slugify(card.getAttribute('data-title') || '') === hash);
      if (targetCard) {
        setTimeout(() => populateAndOpenModalFromCard(targetCard), categoryButton ? 400 : 200);
      }
    }
  </script>
</div></main>

@include('layouts.footer')

</body></html>