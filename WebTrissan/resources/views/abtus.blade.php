<!DOCTYPE html><html lang="id" style=""><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
@php $seoTitle = $pageSettings?->seo_title ?? $globalSettings?->company_name ?? config('app.name'); @endphp
<title>{{ $seoTitle }}</title>
@include('layouts.seo-head', ['seoTitle' => $seoTitle, 'seoDescription' => $pageSettings?->seo_description])
@if(!empty($pageSettings?->seo_description))
<meta name="description" content="{{ $pageSettings->seo_description }}">
@endif
<script src="https://cdn.tailwindcss.com/3.4.17?plugins=forms@0.5.10,container-queries@0.1.1"></script>
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&amp;family=JetBrains+Mono:wght@400;500;600;700&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
<script>
    tailwind.config = {
      theme: {
        extend: { fontSize: {'lg': ['1rem', {lineHeight: '1.6'}], 'xl': ['1.125rem', {lineHeight: '1.5'}], '2xl': ['1.25rem', {lineHeight: '1.4'}], '3xl': ['1.5rem', {lineHeight: '1.3'}], '4xl': ['1.75rem', {lineHeight: '1.25'}], '5xl': ['2.125rem', {lineHeight: '1.15'}], '6xl': ['2.5rem', {lineHeight: '1.1'}]},
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            heading: ['Montserrat', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          },
          colors: {
            brand: {
              navy: '#0d2137',
              navyDark: '#071321',
              blue: '#1e40af',
              accent: '#2563eb',
              lightBlue: '#eff4ff',
              surface: '#f8f9ff',
              border: '#dbeafe',
              grayMuted: '#64748b',
            }
          }
        }
      }
    }
  </script>
<style>
    body {
      background-color: #f8f9ff;
      color: #0b1c30;
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
    }
    .font-heading {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
@include('layouts.scale')
</head>
<body class="bg-[#f8f9ff] min-h-screen antialiased text-slate-800 selection:bg-blue-600 selection:text-white">
@php $cms = fn (string $key): string => (string) $pageSettings?->extra($key); @endphp
@include('layouts.navbar')
<!-- Top Hero / Header Section: Clean & Spacious Architectural Layout -->
<header id="about-hero" class="bg-gradient-to-b from-[#0d2137] via-[#091829] to-[#071321] text-white py-4 md:py-12 px-4 md:px-8 relative overflow-hidden" style="background: linear-gradient(rgb(13, 33, 55) 0%, rgb(9, 24, 41) 50%, rgb(7, 19, 33) calc(100% - 150px), rgb(248, 249, 255) calc(100% - 150px), rgb(248, 249, 255) 100%);">
  <div class="absolute -top-40 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>
  <div class="absolute bottom-10 -left-20 w-[450px] h-[450px] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none"></div>
  <div class="max-w-7xl mx-auto relative z-10">
    <!-- Main Hero Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start mb-6">
      <!-- Left Column -->
      <div class="lg:col-span-6 flex flex-col justify-center lg:pt-20"><nav class="flex items-center gap-2 text-xs font-medium text-slate-300 mb-3 md:mb-6" aria-label="Breadcrumb"><a href="{{ url('/') }}" class="flex items-center gap-1.5 hover:text-white transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg><span class="">Beranda</span></a><span class="text-slate-400">/</span><span class="text-white font-semibold">About Us</span></nav><div class="inline-flex items-center gap-2 px-2.5 py-1 md:px-3 rounded bg-blue-500/10 border border-blue-400/20 text-blue-300 font-mono text-[11px] md:text-xs font-semibold tracking-wider uppercase mb-3 md:mb-6 w-fit">{{ $aboutCompany->hero_badge }}</div><h1 class="text-xl sm:text-3xl md:text-5xl font-extrabold font-heading tracking-tight text-white leading-tight uppercase mb-3 md:mb-7">{{ $aboutCompany->hero_title }}</h1><div class="space-y-2 md:space-y-4 text-slate-300 text-xs sm:text-sm md:text-base leading-relaxed mb-4 md:mb-10 max-w-xl font-normal">@foreach($aboutCompany->hero_paragraphs as $paragraph)<p class="{{ $loop->first ? '' : 'text-slate-400 text-xs sm:text-sm' }}">{{ $paragraph }}</p>@endforeach</div><div class="flex flex-wrap items-center gap-4">@if($aboutCompany->company_profile_pdf_url)<a class="inline-flex items-center gap-2.5 px-5 py-2.5 md:px-6 md:py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-mono font-bold uppercase tracking-wider transition-all duration-200 shadow-lg shadow-blue-900/30 hover:-translate-y-0.5 flex-shrink-0" download href="{{ $aboutCompany->company_profile_pdf_url }}"><span class="">Unduh Company Profile (PDF)</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></a>@endif</div></div>
      <!-- Right Column -->
      <div class="lg:col-span-6 relative pt-6"><div class="relative rounded-2xl p-1.5 md:p-2 bg-gradient-to-b from-slate-700/40 via-slate-800/20 to-slate-800/60 border border-slate-700/70 shadow-2xl backdrop-blur-sm"><div class="relative rounded-xl overflow-hidden aspect-[16/10] bg-slate-900">@if($aboutCompany->hero_image_url)<img alt="{{ $aboutCompany->hero_title }}" fetchpriority="high" decoding="async" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" src="{{ $aboutCompany->hero_image_url }}">@else<div class="w-full h-full flex items-center justify-center text-slate-600"><span aria-hidden="true" class="material-symbols-outlined text-7xl">precision_manufacturing</span></div>@endif<div class="absolute inset-0 bg-gradient-to-t from-[#071321]/90 via-transparent to-black/20 pointer-events-none"></div><div class="absolute top-3 left-3 md:top-4 md:left-4 z-10"><span class="inline-flex items-center gap-1.5 md:gap-2 px-2.5 py-1 md:px-3 md:py-1.5 rounded-md bg-[#0d2137]/90 backdrop-blur-md border border-slate-600/60 text-white font-mono text-[10px] md:text-[11px] font-medium tracking-wide"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> {{ $aboutCompany->image_overlay['plant_badge'] ?? '' }}</span></div><div class="absolute bottom-3 left-3 right-3 md:bottom-4 md:left-4 md:right-4 z-10 flex flex-wrap items-center justify-between gap-2 md:gap-3 p-2.5 md:p-3.5 rounded-lg bg-[#071321]/90 backdrop-blur-md border border-slate-700/80"><div><div class="text-[11px] md:text-xs font-bold font-heading text-white tracking-wide">{{ $aboutCompany->image_overlay['caption_title'] ?? '' }}</div><div class="text-[10px] md:text-[11px] font-mono text-slate-400 mt-0.5">{{ $aboutCompany->image_overlay['caption_subtitle'] ?? '' }}</div></div><div class="inline-flex items-center gap-2 px-2 py-0.5 md:px-2.5 md:py-1 rounded bg-blue-600/30 border border-blue-400/40 text-blue-300 font-mono text-[10px] font-bold">{{ $aboutCompany->image_overlay['tolerance_chip'] ?? '' }}</div></div></div></div><div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4 mt-3 md:mt-4">@foreach(($globalSettings->company_stats ?? []) as $stat)<div class="p-2.5 md:p-3.5 rounded-xl bg-slate-900/60 border border-slate-700/70 backdrop-blur-md"><div class="text-lg md:text-xl font-extrabold font-heading {{ $loop->odd ? 'text-blue-400' : 'text-white' }} tracking-tight leading-tight">{{ $stat['value'] ?? '' }}</div><div class="text-[10px] md:text-[11px] text-slate-300 font-mono uppercase tracking-wider font-medium mt-0.5 md:mt-1">{{ $stat['label_about'] ?? ($stat['label_landing'] ?? '') }}</div></div>@endforeach</div><div class="w-full flex justify-center items-center mt-3 md:mt-4">@if($aboutCompany->partners_image_url)<img src="{{ $aboutCompany->partners_image_url }}" alt="Logo klien perusahaan" loading="lazy" decoding="async" class="w-full max-w-[280px] sm:max-w-xs md:max-w-md h-auto object-contain rounded-xl transition-transform duration-500 hover:scale-105 shadow-sm mx-auto">@endif</div></div>
    </div>
    <!-- Clean Integrated Visi & Misi Section without redundant wrappers -->
    <div class="border-t border-slate-800/60 pt-4">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded bg-blue-500/10 border border-blue-400/30 text-blue-300 font-mono text-xs font-semibold uppercase tracking-wider {{ blank($cms('values_badge')) ? 'invisible' : '' }}">
          <span class="w-2 h-2 rounded-full bg-blue-400"></span> <x-cms-text :value="$cms('values_badge')" />
        </div>
        <x-cms-text :value="$cms('values_subtitle')" class="text-xs font-mono text-slate-400 font-medium" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
@foreach($aboutCompany->vision_mission_values ?? [] as $card)
@php
  $type = $card['card_type'] ?? 'vision';
  $isValues = $type === 'values';
  $body = $card['body'] ?? '';
@endphp
<div class="p-4 md:p-6 rounded-2xl bg-[#0d2137] border border-slate-700/70 shadow-lg flex flex-col justify-between group transition-all hover:-translate-y-0.5 {{ $isValues ? 'hover:border-emerald-400/60' : 'hover:border-blue-400/60' }}">
<div class="space-y-3 md:space-y-4">
<div class="flex items-center justify-between">
<div class="w-8 h-8 md:w-10 md:h-10 rounded-xl {{ $isValues ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-400/30' : 'bg-blue-600/20 text-blue-300 border border-blue-400/30' }} flex items-center justify-center shadow-inner">
@if($type === 'mission')
<svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
@elseif($isValues)
<svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
@else
<svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
@endif
</div>
<span class="text-[10px] md:text-[11px] font-mono tracking-wider uppercase {{ $isValues ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-400/30' : 'text-blue-300 bg-blue-500/10 border border-blue-400/30' }} px-2 py-0.5 md:px-2.5 md:py-1 rounded-md font-semibold">{{ $card['badge'] ?? '' }}</span>
</div>
<h3 class="text-base md:text-lg font-bold text-white font-heading tracking-tight">{{ $card['title'] ?? '' }}</h3>
@if($type === 'mission')
<ul class="text-xs text-slate-300 space-y-1.5 md:space-y-2 leading-relaxed">
@foreach((array) $body as $point)
<li class="flex items-start gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></span><span class="">{{ $point }}</span></li>
@endforeach
</ul>
@elseif($isValues)
<div class="space-y-1.5 md:space-y-2 text-xs">
@foreach((array) $body as $value)
<div class="flex items-baseline justify-between gap-3 p-1.5 rounded bg-slate-800/60 border border-slate-700/60"><span class="font-semibold text-slate-200">{{ $value['name'] ?? '' }}:</span><span class="{{ ['text-blue-300', 'text-emerald-400', 'text-slate-300'][$loop->index % 3] }} font-mono text-[11px] text-right">{{ $value['value'] ?? '' }}</span></div>
@endforeach
</div>
@else
<p class="text-xs text-slate-300 leading-relaxed font-normal">{{ is_array($body) ? implode(' ', $body) : $body }}</p>
@endif
</div>
@if(!empty($card['footer_label']) || !empty($card['footer_value']))
<div class="pt-3 md:pt-4 mt-4 md:mt-6 border-t border-slate-700/70 flex items-center justify-between text-[10px] md:text-[11px] font-mono text-slate-400"><span class="text-slate-400">{{ $card['footer_label'] ?? '' }}:</span><span class="{{ $isValues ? 'text-emerald-400 bg-emerald-500/10 border-emerald-400/30' : 'text-blue-300 bg-blue-900/30 border-blue-400/20' }} font-semibold px-2 py-0.5 rounded border">{{ $card['footer_value'] ?? '' }}</span></div>
@endif
</div>
@endforeach
</div>
    </div>
  </div>
</header>
<!-- Section 1: Profil Perusahaan, Visi & Misi (Spacious & Clean Cards) -->

<!-- Section 2: Fasilitas & Infrastruktur Pabrik (Proportional 16:9 Aspect Ratio) -->
<section class="py-8 md:py-14 px-4 md:px-8 lg:px-12 bg-[#f8f9ff] border-b border-slate-200" id="fasilitas">
<div class="max-w-7xl mx-auto">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 md:mb-12">
<div>
<div class="inline-flex items-center gap-2.5 px-3 py-1 rounded bg-white border border-slate-200 text-brand-navy font-mono text-xs font-semibold uppercase tracking-wider mb-4 {{ blank($cms('facilities_badge')) ? 'invisible' : '' }}">
<span class="w-2 h-2 bg-brand-navy rounded-sm"></span>
<x-cms-text :value="$cms('facilities_badge')" />
</div>
<x-cms-text tag="h2" :value="$cms('facilities_title')" class="block text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy font-heading tracking-tight leading-tight" />
<x-cms-text tag="p" :value="$cms('facilities_description')" class="block text-slate-600 text-sm md:text-base mt-2 max-w-2xl" />
</div>
<div class="flex items-center gap-3"><div class="flex items-center gap-1.5"><button type="button" onclick="document.getElementById('facility-carousel').scrollBy({left: -380, behavior: 'smooth'})" aria-label="Previous Facility" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:border-blue-400 hover:text-blue-600 text-slate-700 flex items-center justify-center transition-all shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button><button type="button" onclick="document.getElementById('facility-carousel').scrollBy({left: 380, behavior: 'smooth'})" aria-label="Next Facility" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:border-blue-400 hover:text-blue-600 text-slate-700 flex items-center justify-center transition-all shadow-sm"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button></div></div>
</div>
<div id="facility-carousel" class="flex overflow-x-auto snap-x snap-mandatory md:grid gap-4 md:gap-6 pb-3 md:grid-cols-3 pb-12">
@foreach(collect($aboutCompany->facilities ?? [])->sortBy('sort_order') as $facility)
@php
  $facilityImage = \App\Support\MediaUrl::resolve($facility['facility_image'] ?? null);
@endphp
<div class="min-w-[280px] sm:min-w-[320px] md:min-w-0 max-w-[300px] sm:max-w-[340px] md:max-w-none snap-start bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col group flex-shrink-0 md:flex-shrink">
<div class="aspect-[16/9] w-full overflow-hidden relative bg-slate-900">
@if($facilityImage)
<img alt="{{ $facility['facility_title'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" src="{{ $facilityImage }}">
@else
<div class="w-full h-full flex items-center justify-center text-slate-600"><span aria-hidden="true" class="material-symbols-outlined text-6xl">factory</span></div>
@endif
<span class="absolute top-2.5 left-2.5 md:top-3.5 md:left-3.5 bg-brand-navy/90 backdrop-blur-sm text-white font-mono text-[9px] md:text-[10px] px-2 py-0.5 md:px-2.5 md:py-1 rounded font-semibold tracking-wide border border-slate-700">{{ $facility['facility_tag'] ?? '' }}</span>
</div>
<div class="p-4 md:p-6 flex-1 flex flex-col justify-between">
<div>
<h3 class="font-bold text-brand-navy text-base md:text-lg font-heading mb-1.5 md:mb-2">{{ $facility['facility_title'] ?? '' }}</h3>
<p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4 md:mb-6">{{ $facility['facility_description'] ?? '' }}</p>
</div>
<div class="pt-3 md:pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-mono text-slate-500"><div class="flex flex-wrap items-center gap-2">
@foreach(array_filter([$facility['spec_tag_1'] ?? null, $facility['spec_tag_2'] ?? null]) as $specTag)
<span class="inline-flex items-center px-2.5 py-1 rounded bg-blue-50 text-blue-900 border border-blue-200 text-xs font-mono font-medium">{{ $specTag }}</span>
@endforeach
</div></div>
</div>
</div>
@endforeach
</div>
</div>
</section>
<!-- Section 3: Company Milestone Per Year (Minimalist Architectural Vertical Timeline) -->
<section class="py-8 md:py-14 px-4 md:px-8 lg:px-12 bg-white border-b border-slate-200">
<div class="max-w-5xl mx-auto">
<div class="text-center max-w-2xl mx-auto mb-16">
<div class="inline-flex items-center gap-2.5 px-3 py-1 rounded bg-slate-100 border border-slate-200 text-brand-navy font-mono text-xs font-semibold uppercase tracking-wider mb-4 {{ blank($cms('milestones_badge')) ? 'invisible' : '' }}">
<span class="w-2 h-2 bg-brand-navy rounded-sm"></span>
<x-cms-text :value="$cms('milestones_badge')" />
</div>
<x-cms-text tag="h2" :value="$cms('milestones_title')" class="block text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy font-heading tracking-tight leading-tight" />
<x-cms-text tag="p" :value="$cms('milestones_description')" class="block text-slate-600 text-sm md:text-base mt-3 leading-relaxed" />
</div>
<!-- Clean Minimal Timeline with generous vertical breathing room -->
<div class="relative pl-6 md:pl-0">
<!-- Center/Left continuous vertical guide line -->
<div class="absolute left-6 md:left-1/2 top-4 bottom-4 w-[2px] bg-slate-200 transform md:-translate-x-1/2"></div>
<div class="space-y-8 md:space-y-10">
@foreach(collect($aboutCompany->milestones ?? [])->sortBy('sort_order')->values() as $milestone)
@php
  $isCurrent = (bool) ($milestone['is_current'] ?? false);
  $textOnLeft = $loop->odd;
  $nodeClasses = $isCurrent ? 'bg-emerald-500 border-4 border-white shadow-md' : ($loop->odd ? 'bg-white border-4 border-[#0d2137]' : 'bg-white border-4 border-blue-600');
@endphp
<div class="relative flex flex-col md:flex-row items-start md:items-center">
@unless($textOnLeft)
<div class="hidden md:block md:w-1/2 md:pr-14"></div>
@endunless
<div class="absolute left-6 md:left-1/2 transform -translate-x-1/2 w-4 h-4 rounded-full {{ $nodeClasses }} z-10"></div>
<div class="w-full md:w-1/2 pl-10 {{ $textOnLeft ? 'md:pr-14 md:pl-0 md:text-right' : 'md:pl-14 md:text-left' }}">
<span class="inline-block px-3 py-1 rounded {{ $isCurrent ? 'bg-blue-600' : 'bg-[#0d2137]' }} text-white font-mono text-xs font-bold tracking-wider mb-2">{{ $milestone['year'] ?? '' }}</span>
<h3 class="text-lg font-bold text-brand-navy font-heading">{{ $milestone['title'] ?? '' }}</h3>
<p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $milestone['description'] ?? '' }}</p>
@if(!empty($milestone['highlights']))
<div class="mt-3 inline-block text-xs font-mono {{ $isCurrent ? 'text-blue-900 bg-blue-50 border-blue-200' : 'text-slate-500 bg-slate-50 border-slate-200' }} px-3 py-1 rounded border">{{ collect($milestone['highlights'])->filter()->take(2)->map(fn ($highlight) => '• '.$highlight)->implode(' ') }}</div>
@endif
</div>
@if($textOnLeft)
<div class="hidden md:block md:w-1/2 md:pl-14"></div>
@endif
</div>
@endforeach
</div></div></div></section>
<!-- Section 4: Bagan Pohon Struktur Organisasi (Clean Hierarchy Tree) -->
<section class="px-4 md:px-8 lg:px-12 bg-[#f8f9ff] pt-8 md:pt-14 pb-16 md:py-20">
<div class="max-w-7xl mx-auto">
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 md:mb-12">
<div>
<div class="inline-flex items-center gap-2.5 px-3 py-1 rounded bg-white border border-slate-200 text-brand-navy font-mono text-xs font-semibold uppercase tracking-wider mb-4 {{ blank($cms('structure_badge')) ? 'invisible' : '' }}">
<span class="w-2 h-2 bg-brand-navy rounded-sm"></span>
<x-cms-text :value="$cms('structure_badge')" />
</div>
<x-cms-text tag="h2" :value="$cms('structure_title')" class="block text-2xl sm:text-3xl lg:text-4xl font-extrabold text-brand-navy font-heading tracking-tight leading-tight" />
<x-cms-text tag="p" :value="$cms('structure_description')" class="block text-slate-600 text-sm md:text-base mt-2 max-w-2xl" />
</div>
<div class="text-xs font-mono text-slate-500 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 w-fit">
          Dokumen Resmi: <strong class="text-slate-800">{{ $aboutCompany->org_chart['chart_revision_tag'] ?? '-' }}</strong>
</div>
</div>
<!-- Org Tree Diagram: nested levels joined by connector lines, built from the CMS organization nodes -->
@php $orgTree = $aboutCompany->org_tree; @endphp
@if(count($orgTree) > 0)
<style>
#org-chart{--org-line:#1e4e8c;--org-fill:#0b57d0;--org-border:#08306b}
#org-chart .org-tree{display:table;margin:0 auto;padding:4px 8px}
#org-chart ul{display:flex;justify-content:center;margin:0;padding:0;position:relative;list-style:none}
#org-chart li{list-style:none;position:relative;display:flex;flex-direction:column;align-items:center;padding:24px 4px 0}
#org-chart .org-tree>ul>li{padding-top:0}
#org-chart li ul{padding-top:24px}
#org-chart li ul::before{content:'';position:absolute;top:0;left:50%;margin-left:-1px;height:24px;border-left:2px solid var(--org-line)}
#org-chart li li::before,#org-chart li li::after{content:'';position:absolute;top:0;right:50%;width:50%;height:24px;border-top:2px solid var(--org-line)}
#org-chart li li::after{right:auto;left:50%;margin-left:-1px;border-left:2px solid var(--org-line)}
#org-chart li li:only-child{padding-top:0}
#org-chart li li:only-child::before,#org-chart li li:only-child::after{display:none}
#org-chart li li:first-child::before,#org-chart li li:last-child::after{border:0 none}
#org-chart li li:last-child::before{margin-right:-1px;border-right:2px solid var(--org-line)}
#org-chart .org-wrap{position:relative;display:inline-block}
#org-chart .org-card{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;text-align:center;color:#fff;background:var(--org-fill);border:2px solid var(--org-border);border-radius:10px;box-shadow:0 4px 10px rgba(8,48,107,.25);padding:8px 10px}
#org-chart .org-name{font-weight:700;line-height:1.25}
#org-chart .org-position{color:#dbeafe;line-height:1.25}
#org-chart .org-d0,#org-chart .org-d1,#org-chart .org-d2{min-width:240px}
#org-chart .org-d0 .org-name,#org-chart .org-d1 .org-name,#org-chart .org-d2 .org-name{font-size:15px}
#org-chart .org-d0 .org-position,#org-chart .org-d1 .org-position,#org-chart .org-d2 .org-position{font-size:12px}
#org-chart .org-d3{min-width:150px}
#org-chart .org-d3 .org-name{font-size:13px}
#org-chart .org-d3 .org-position{font-size:11px}
#org-chart .org-d4{width:116px;padding:6px 5px}
#org-chart .org-d4 .org-name{font-size:12px}
#org-chart .org-d4 .org-position{font-size:10.5px}
#org-chart .org-photo{width:36px;height:36px;border-radius:9999px;object-fit:cover;border:2px solid #bfdbfe;margin-bottom:2px}
#org-chart .org-side{position:absolute;left:100%;top:50%;transform:translateY(-50%);display:flex;align-items:center;width:max-content}
#org-chart .org-side-line{display:block;flex:0 0 36px;height:2px;background:var(--org-line)}
</style>
<div class="bg-white p-4 md:p-8 rounded-2xl border border-slate-200 shadow-sm">
<p class="lg:hidden text-xs text-slate-500 mb-3">Geser ke samping untuk melihat seluruh bagan.</p>
<div id="org-chart" class="overflow-x-auto pb-2">
<div id="org-tree" class="org-tree" aria-label="Bagan struktur organisasi">
<ul>
@foreach($orgTree as $rootNode)
<x-org-node :node="$rootNode" />
@endforeach
</ul>
</div>
</div>
</div>
<script>
(function () {
  const box = document.getElementById('org-chart');
  const tree = document.getElementById('org-tree');
  if (!box || !tree) return;
  function fit() {
    tree.style.zoom = '';
    if (window.innerWidth >= 1024 && tree.scrollWidth > box.clientWidth) {
      tree.style.zoom = Math.max(box.clientWidth / tree.scrollWidth, 0.75);
    }
  }
  window.addEventListener('load', fit);
  window.addEventListener('resize', fit);
  fit();
})();
</script>
@endif
</div></section>
<!-- CTA Section: Balanced & Spacious Architectural Finish -->

@include('layouts.footer')
<!-- Clean Bottom Bar -->















</body></html>