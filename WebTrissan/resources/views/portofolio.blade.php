<!DOCTYPE html><html lang="id" style=""><head><meta charset="utf-8"><meta content="width=device-width, initial-scale=1.0" name="viewport">
@php $seoTitle = $pageSettings?->seo_title ?? $globalSettings?->company_name ?? config('app.name'); @endphp
<title>{{ $seoTitle }}</title>
@include('layouts.seo-head', ['seoTitle' => $seoTitle, 'seoDescription' => $pageSettings?->seo_description])
@if(!empty($pageSettings?->seo_description))
<meta name="description" content="{{ $pageSettings->seo_description }}">
@endif<meta content="web_standard" name="shell-type"><link href="https://fonts.googleapis.com" rel="preconnect"><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Montserrat:wght@600;700;800&amp;display=swap" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"><style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}</style><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">tailwind.config = { darkMode: "class", theme: { extend: { fontSize: {'lg': ['1rem', {lineHeight: '1.6'}], 'xl': ['1.125rem', {lineHeight: '1.5'}], '2xl': ['1.25rem', {lineHeight: '1.4'}], '3xl': ['1.5rem', {lineHeight: '1.3'}], '4xl': ['1.75rem', {lineHeight: '1.25'}], '5xl': ['2.125rem', {lineHeight: '1.15'}], '6xl': ['2.5rem', {lineHeight: '1.1'}]},"colors": { "surface-container": "#e5eeff", "surface-container-highest": "#d3e4fe", "surface-container-high": "#dce9ff", "on-tertiary-container": "#7a899d", "secondary-fixed": "#c8e6ff", "secondary": "#416279", "surface-bright": "#f8f9ff", "primary-fixed": "#d2e4ff", "on-secondary-container": "#45667d", "surface-tint": "#4d6079", "error-container": "#ffdad6", "tertiary-fixed": "#d4e4fa", "tertiary-container": "#122131", "background": "#f8f9ff", "surface-container-low": "#eff4ff", "surface-variant": "#d3e4fe", "secondary-fixed-dim": "#a9cbe5", "on-primary-container": "#7689a4", "on-tertiary-fixed-variant": "#39485a", "primary-fixed-dim": "#b5c8e5", "on-surface": "#0b1c30", "surface-dim": "#cbdbf5", "on-primary-fixed-variant": "#364860", "outline-variant": "#c4c6cd", "on-secondary": "#ffffff", "inverse-on-surface": "#eaf1ff", "tertiary-fixed-dim": "#b9c8de", "primary-container": "#0d2137", "on-error-container": "#93000a", "on-error": "#ffffff", "primary": "#000917", "inverse-primary": "#b5c8e5", "on-tertiary-fixed": "#0d1c2d", "on-secondary-fixed": "#001e2e", "surface": "#f8f9ff", "error": "#ba1a1a", "on-tertiary": "#ffffff", "outline": "#74777d", "secondary-container": "#c2e4ff", "on-surface-variant": "#44474d", "on-primary-fixed": "#081c32", "surface-container-lowest": "#ffffff", "on-primary": "#ffffff", "on-background": "#0b1c30", "tertiary": "#000916", "inverse-surface": "#213145", "on-secondary-fixed-variant": "#284a60" }, "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" }, "spacing": { "gutter-desktop": "1.5rem", "margin-desktop": "3rem", "space-md": "1rem", "space-sm": "0.5rem", "space-xl": "2rem", "space-2xl": "3rem", "space-lg": "1.5rem", "space-3xl": "4rem", "margin-tablet": "2rem", "space-xs": "0.25rem", "gutter": "1.5rem", "margin": "1rem" }, "fontFamily": { "headline-md": ["Montserrat"], "title-md": ["Inter"], "body-md": ["Inter"], "headline-lg": ["Montserrat"], "label-sm": ["Inter"], "label-md": ["Inter"], "body-sm": ["Inter"], "body-lg": ["Inter"], "headline-sm": ["Montserrat"], "headline-lg-mobile": ["Montserrat"], "display-hero": ["Montserrat"], "label-caps": ["Montserrat"], "display-hero-mobile": ["Montserrat"] }, "fontSize": { "headline-md": ["22px", { "lineHeight": "30px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "title-md": ["16px", { "lineHeight": "24px", "fontWeight": "600" }], "body-md": ["15px", { "lineHeight": "24px", "fontWeight": "400" }], "headline-lg": ["30px", { "lineHeight": "38px", "letterSpacing": "-0.015em", "fontWeight": "700" }], "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "500" }], "label-md": ["14px", { "lineHeight": "20px", "fontWeight": "500" }], "body-sm": ["14px", { "lineHeight": "20px", "fontWeight": "400" }], "body-lg": ["16px", { "lineHeight": "26px", "fontWeight": "400" }], "headline-sm": ["18px", { "lineHeight": "26px", "fontWeight": "600" }], "headline-lg-mobile": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "700" }], "display-hero": ["40px", { "lineHeight": "48px", "letterSpacing": "-0.02em", "fontWeight": "800" }], "label-caps": ["12px", { "lineHeight": "16px", "letterSpacing": "0.08em", "fontWeight": "700" }], "display-hero-mobile": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.02em", "fontWeight": "800" }] } } } };</script>@include('layouts.scale')
</head><body class="bg-surface font-body-md text-body-md text-on-surface antialiased">
@php $cms = fn (string $key): string => (string) $pageSettings?->extra($key); @endphp
@include('layouts.navbar')
<main class="w-full bg-surface pb-space-xl"><div class="w-full flex flex-col"><!-- SEKSI 1: HEADER & HERO (Biru Muda / surface-container-low) --><section class="w-full bg-surface-container-low border-b border-surface-container pb-space-md md:pb-space-xl"><div class="max-w-[1360px] mx-auto px-margin md:px-margin-desktop flex flex-col w-full"><div class="w-full pb-space-sm md:pb-space-md flex flex-wrap items-center justify-between gap-space-xs md:gap-space-sm border-b border-outline-variant/20 pt-space-lg md:pt-space-2xl"><nav class="flex items-center gap-space-xs font-label-caps text-label-caps tracking-widest text-on-surface-variant"><a class="hover:text-primary transition-colors" href="{{ url('/') }}">Beranda</a><span class="text-secondary/40 font-bold">/</span><span class="text-primary font-semibold">Portofolio</span></nav></div><div class="pt-space-sm md:pt-space-lg"><div class="flex flex-col lg:flex-row lg:items-end justify-between gap-space-md md:gap-space-lg pb-space-xs md:pb-space-md"><div class="max-w-3xl"><x-cms-text tag="h1" :value="$pageSettings?->hero_title" class="block font-headline-lg text-[26px] sm:text-headline-lg text-primary tracking-tight leading-tight" /><x-cms-text tag="p" :value="$pageSettings?->hero_subtitle" class="block font-body-md text-body-md text-on-surface-variant mt-space-xs md:mt-space-sm leading-relaxed" /></div></div><div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-space-sm md:gap-space-md mt-space-md md:mt-space-lg">
@if(!empty($pageSettings?->stats))
  @foreach($pageSettings->stats as $stat)
  <div class="bg-surface-container-lowest p-3 md:p-space-md rounded-lg shadow-sm border border-outline-variant/20 flex flex-col justify-between @if($loop->last && $loop->iteration == 5) col-span-2 md:col-span-1 @endif">
    <div class="font-label-caps text-label-caps text-on-surface-variant flex items-center justify-between">
      <span class="">{{ $stat['label'] ?? '' }}</span>
      <span aria-hidden="true" class="material-symbols-outlined text-[16px] text-secondary">{{ $stat['icon'] ?? 'analytics' }}</span>
    </div>
    <div class="font-headline-md text-headline-md text-primary mt-1 md:mt-2">{{ $stat['value'] ?? '' }}</div>
    <div class="font-label-sm text-label-sm text-secondary font-medium mt-0.5 md:mt-1">{{ $stat['caption'] ?? '' }}</div>
  </div>
  @endforeach
@endif
</div></div></div></section><!-- SEKSI 2: SHOWCASE KATALOG HASIL KERJA (Putih Bersih / surface-container-lowest) --><section class="w-full bg-surface-container-lowest pt-space-lg md:pt-space-2xl pb-space-md md:pb-space-lg"><div class="max-w-[1360px] mx-auto px-margin md:px-margin-desktop"><div class="flex flex-col md:flex-row md:items-end justify-between mb-space-md md:mb-space-lg gap-space-sm md:gap-space-md"><div><div class="flex items-center gap-space-xs text-secondary font-label-caps text-label-caps mb-1 {{ blank($cms('catalog_badge')) ? 'invisible' : '' }}"><span aria-hidden="true" class="material-symbols-outlined text-[16px]">inventory_2</span> <x-cms-text :value="$cms('catalog_badge')" /></div><x-cms-text tag="h2" :value="$cms('catalog_title')" class="block font-headline-md text-headline-md text-primary tracking-tight" /></div><x-cms-text tag="p" :value="$cms('catalog_description')" class="block font-body-sm text-body-sm text-on-surface-variant max-w-md" /></div><div class="flex items-center gap-space-xs overflow-x-auto pb-space-sm mb-space-md md:mb-space-lg text-label-sm font-label-sm whitespace-nowrap" id="projectFilterTabs"><button class="portfolio-tab px-space-md py-2 rounded-lg bg-primary text-on-primary font-semibold whitespace-nowrap shadow-sm flex items-center gap-2 shrink-0" data-category="all" type="button"><span aria-hidden="true" class="material-symbols-outlined text-[16px]">apps</span> Semua Hasil Kerja ({{ $portfolioItems->count() }})</button>
@foreach($portfolioCategories as $category)
<button class="portfolio-tab px-space-md py-2 rounded-lg bg-surface-container-low text-on-surface-variant border border-outline-variant/30 hover:bg-surface-container-high transition-colors whitespace-nowrap shrink-0" data-category="{{ $category->id }}" type="button">{{ $category->name }}</button>
@endforeach</div><div id="deliverablesGrid" class="grid grid-cols-2 lg:grid-cols-4 gap-space-sm md:gap-space-md">
@foreach($portfolioItems as $item)
<div class="deliverable-card group relative bg-surface-container-lowest rounded-xl overflow-hidden border border-outline-variant/30 shadow-sm hover:shadow-md transition-all cursor-pointer flex flex-col justify-between" data-category="{{ $item->portfolio_category_id }}" data-title="{{ $item->title }}" data-desc="{{ $item->description }}" data-image="{{ $item->image_url }}">
<div class="relative h-24 md:h-40 w-full overflow-hidden bg-surface-container">
@if($item->image_url)
<img alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" src="{{ $item->image_url }}">
@else
<div class="w-full h-full flex items-center justify-center text-outline-variant"><span aria-hidden="true" class="material-symbols-outlined text-5xl">precision_manufacturing</span></div>
@endif
<div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"><span class="p-2 rounded-full bg-surface-container-lowest/90 text-primary shadow-sm backdrop-blur-sm flex items-center justify-center"><span aria-hidden="true" class="material-symbols-outlined text-[18px]">zoom_in</span></span></div>
</div>
<div class="p-2 md:p-3 bg-surface-container-lowest border-t border-outline-variant/20 flex items-center justify-between gap-1"><div><span class="font-label-caps text-[11px] text-secondary font-bold block uppercase tracking-wider mb-0.5">{{ $item->industry_tag }}</span><h3 class="font-label-md text-label-md text-primary font-bold group-hover:text-secondary transition-colors line-clamp-1">{{ $item->title }}</h3></div><span aria-hidden="true" class="material-symbols-outlined text-secondary text-[16px] shrink-0">open_in_new</span></div>
</div>
@endforeach
</div><div class="hidden py-space-xl text-center text-on-surface-variant font-body-sm text-body-sm" id="deliverablesEmpty">Belum ada hasil kerja pada kategori ini.</div><div class="mt-space-md md:mt-space-lg flex flex-col sm:flex-row items-center justify-between gap-space-sm md:gap-space-md bg-surface-container-low p-3 md:p-space-md rounded-xl border border-outline-variant/20 shadow-sm"><div class="font-body-sm text-body-sm text-on-surface-variant" id="deliverablesInfo"></div><div class="flex items-center gap-space-xs" id="deliverablesPager"></div></div></div></section><!-- SEKSI 3: 4 KLASTER KAPABILITAS MANUFAKTUR (Biru Muda / surface-container-low) --><!-- SEKSI 4: MATRIKS DIREKTORI 15 PERSONIL (Putih Bersih / surface-container-lowest) --><section class="w-full bg-surface-container-lowest pt-space-md md:pt-space-lg"><div class="max-w-[1360px] mx-auto px-margin md:px-margin-desktop"><div class="flex flex-col md:flex-row md:items-end justify-between mb-space-md md:mb-space-lg gap-space-sm md:gap-space-md"><div><div class="flex items-center gap-space-xs text-secondary font-label-caps text-label-caps mb-1 {{ blank($cms('personnel_badge')) ? 'invisible' : '' }}"><span aria-hidden="true" class="material-symbols-outlined text-[16px]">groups</span> <x-cms-text :value="$cms('personnel_badge')" /></div><h2 class="font-headline-md text-headline-md text-primary tracking-tight">Matriks Direktori {{ $personnel->count() }} Personil Terdaftar</h2></div><x-cms-text :value="$pageSettings?->registry_revision_tag" class="font-label-caps text-label-caps text-on-surface-variant font-mono bg-surface-container-low px-3 py-1.5 rounded-md border border-outline-variant/30 self-start md:self-auto {{ blank($pageSettings?->registry_revision_tag) ? 'invisible' : '' }}" /></div><div class="bg-surface-container-low p-3 md:p-space-md rounded-xl border border-outline-variant/30 shadow-sm mb-space-md md:mb-space-lg flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-space-sm md:gap-space-md"><div class="relative flex-1"><span aria-hidden="true" class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span><input class="w-full pl-10 pr-4 py-2 md:py-2.5 bg-surface-container-lowest rounded-lg text-body-sm font-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-bright focus:ring-1 focus:ring-primary transition-all border border-outline-variant/20" id="personnelSearch" placeholder="Cari personil, kode lisensi (ASME, ASNT, Fanuc), atau spesialisasi..." type="text"></div><div class="flex items-center gap-space-xs md:gap-space-sm shrink-0 flex-wrap justify-between sm:justify-start"><div class="relative flex-1 sm:flex-initial"><select id="personnelSort" class="w-full sm:w-auto appearance-none bg-surface-container-lowest border border-outline-variant/20 text-on-surface font-label-md text-label-md py-2 md:py-2.5 pl-4 pr-10 rounded-lg cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary"><option value="registry">Urutkan: ID Registrasi</option><option value="rank">Urutkan: Kualifikasi Tertinggi</option><option value="cluster">Urutkan: Klaster Spesialisasi</option></select><span aria-hidden="true" class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-[20px]">expand_more</span></div><div class="text-label-sm font-semibold text-secondary px-3 py-2 bg-surface-container-lowest border border-outline-variant/20 rounded-lg whitespace-nowrap">{{ $personnel->count() }} Rekayasa Aktif Terverifikasi</div></div></div><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-sm md:gap-space-md" id="personnelGrid">
@foreach($personnel as $person)
@php
  $skills = $person->skills->take(3);
  $searchBlob = \Illuminate\Support\Str::lower(implode(' ', array_filter([
    $person->registration_id, $person->job_role, $person->technical_focus,
    $person->specialtyCluster?->name, ...$skills->pluck('skill_name')->all(),
  ])));
@endphp
<div class="personnel-card bg-surface-container-lowest rounded-lg p-3 md:p-space-md border border-outline-variant/20 shadow-sm flex flex-col justify-between hover:shadow-md transition-all" data-order="{{ $loop->index }}" data-rank="{{ $person->qualification_rank }}" data-cluster="{{ $person->specialtyCluster?->name }}" data-search="{{ $searchBlob }}">
<div>
<div class="flex items-center justify-between mb-1.5 md:mb-2"><span class="font-label-caps text-label-caps bg-primary text-on-primary px-2 py-0.5 rounded font-bold">REG: {{ $person->registration_id }}</span>
@if($person->status)
<span class="font-label-sm text-label-sm text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1 font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Aktif</span>
@else
<span class="font-label-sm text-label-sm text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full flex items-center gap-1 font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>Non-Aktif</span>
@endif
</div>
<h4 class="font-title-md text-title-md text-primary font-bold leading-tight">{{ $person->job_role }}</h4>
<p class="font-label-sm text-label-sm text-secondary font-medium mb-1 md:mb-space-sm">{{ $person->specialtyCluster?->name }}</p>
<div class="flex flex-wrap gap-1 mb-2 md:mb-space-sm">
@foreach($skills as $skill)
<span class="bg-surface-container-low text-on-surface-variant px-2 py-0.5 rounded text-label-sm font-label-sm">{{ $skill->skill_name }}</span>
@endforeach
</div>
</div>
@if($person->technical_focus)
<div class="pt-2 border-t border-surface-container"><div class="font-label-sm text-label-sm text-on-surface-variant flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5 sm:gap-2"><span class="text-secondary font-medium">Fokus Teknis:</span><span class="font-semibold text-on-surface sm:text-right">{{ $person->technical_focus }}</span></div></div>
@endif
</div>
@endforeach
</div><div class="hidden py-space-xl text-center text-on-surface-variant font-body-sm text-body-sm" id="personnelEmpty">Tidak ada personil yang cocok dengan pencarian Anda.</div><div class="mt-space-md md:mt-space-lg flex flex-col sm:flex-row items-center justify-between gap-space-sm md:gap-space-md bg-surface-container-low p-3 md:p-space-md rounded-xl border border-outline-variant/20 shadow-sm"><div class="font-body-sm text-body-sm text-on-surface-variant" id="personnelInfo"></div><div class="flex items-center gap-space-xs" id="personnelPager"></div></div></div></section></div>
</main>
@include('layouts.footer')
<!-- Lightbox Modal Component -->
<div id="deliverableModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-primary/80 backdrop-blur-sm transition-all duration-300 hidden" onclick="closeDeliverableModal(event)"><div class="relative bg-surface-container-lowest rounded-xl max-w-3xl w-full overflow-hidden shadow-2xl border border-outline-variant/30 flex flex-col" onclick="event.stopPropagation()"><div class="relative w-full bg-surface-container-low max-h-[60vh] flex items-center justify-center overflow-hidden"><img id="modalImage" alt="" class="w-full h-auto max-h-[60vh] object-contain rounded-t-xl hidden"><div class="hidden w-full h-48 flex items-center justify-center text-outline-variant" id="modalImagePlaceholder"><span aria-hidden="true" class="material-symbols-outlined text-6xl">precision_manufacturing</span></div><button onclick="hideDeliverableModal()" aria-label="Tutup Modal" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-surface-container-lowest/90 hover:bg-surface-container-highest text-primary shadow-md flex items-center justify-center transition-colors z-10 backdrop-blur-sm"><span aria-hidden="true" class="material-symbols-outlined text-[20px]">close</span></button></div><div class="p-space-lg flex flex-col"><h3 id="modalTitle" class="font-headline-sm text-headline-sm text-primary tracking-tight font-bold mb-2"></h3><p id="modalDesc" class="font-body-md text-body-md text-on-surface-variant leading-relaxed"></p></div></div></div>

<script>
  const pagerClasses = {
    active: 'w-9 md:w-10 h-9 md:h-10 rounded-lg bg-primary text-on-primary font-bold font-label-md text-label-md shadow-sm',
    idle: 'w-9 md:w-10 h-9 md:h-10 rounded-lg bg-surface-container-lowest text-on-surface hover:bg-surface-container-high font-label-md text-label-md transition-colors border border-outline-variant/20',
    arrow: 'px-3 md:px-space-md py-1.5 md:py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container-high text-on-surface font-label-md text-label-md flex items-center gap-1 transition-colors border border-outline-variant/20 disabled:text-outline-variant disabled:cursor-not-allowed disabled:hover:bg-surface-container-lowest',
  };

  function pagerButton(className, label, onClick, disabled = false, icon = null) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = className;
    button.disabled = disabled;
    if (icon) {
      const glyph = document.createElement('span');
      glyph.className = 'material-symbols-outlined text-[18px]';
      glyph.textContent = icon;
      const text = document.createElement('span');
      text.textContent = label;
      button.append(...(icon === 'chevron_left' ? [glyph, text] : [text, glyph]));
    } else {
      button.textContent = label;
    }
    button.addEventListener('click', onClick);
    return button;
  }

  // Shared client-side list controller: filter -> sort -> paginate over server-rendered elements.
  function createListController({ container, items, perPage, infoEl, navEl, emptyEl, unit, matches, compare }) {
    const order = new Map(items.map((item, index) => [item, index]));
    let page = 1;

    function render() {
      const matched = items.filter(matches);
      if (compare) matched.sort((a, b) => compare(a, b) || order.get(a) - order.get(b));
      const total = matched.length;
      const pages = Math.max(1, Math.ceil(total / perPage));
      page = Math.min(Math.max(page, 1), pages);
      const start = (page - 1) * perPage;
      const visible = new Set(matched.slice(start, start + perPage));

      matched.forEach((item) => container.appendChild(item));
      items.forEach((item) => item.classList.toggle('hidden', !visible.has(item)));
      emptyEl?.classList.toggle('hidden', total !== 0);

      infoEl.replaceChildren();
      if (total === 0) {
        infoEl.textContent = `Tidak ada ${unit} yang sesuai`;
      } else {
        const strong = (text) => {
          const el = document.createElement('strong');
          el.className = 'text-on-surface font-semibold';
          el.textContent = text;
          return el;
        };
        infoEl.append('Menampilkan ', strong(`${start + 1} - ${start + visible.size}`), ' dari ', strong(String(total)), ` ${unit}`);
      }

      const go = (target) => () => { page = target; render(); container.scrollIntoView({ behavior: 'smooth', block: 'start' }); };
      navEl.replaceChildren(
        pagerButton(pagerClasses.arrow, 'Previous', go(page - 1), page === 1, 'chevron_left'),
        ...Array.from({ length: pages }, (_, i) => pagerButton(i + 1 === page ? pagerClasses.active : pagerClasses.idle, String(i + 1), go(i + 1))),
        pagerButton(pagerClasses.arrow, 'Next', go(page + 1), page === pages, 'chevron_right')
      );
    }

    return { render, reset() { page = 1; render(); } };
  }

  // Deliverables: category tabs + modal
  const deliverableCards = Array.from(document.querySelectorAll('.deliverable-card'));
  const tabButtons = Array.from(document.querySelectorAll('.portfolio-tab'));
  let activeCategory = 'all';

  const deliverables = createListController({
    container: document.getElementById('deliverablesGrid'),
    items: deliverableCards,
    perPage: 12,
    infoEl: document.getElementById('deliverablesInfo'),
    navEl: document.getElementById('deliverablesPager'),
    emptyEl: document.getElementById('deliverablesEmpty'),
    unit: 'Hasil Kerja Rekayasa',
    matches: (card) => activeCategory === 'all' || card.getAttribute('data-category') === activeCategory,
  });

  tabButtons.forEach((button) => button.addEventListener('click', () => {
    tabButtons.forEach((b) => {
      b.classList.remove('bg-primary', 'text-on-primary', 'font-semibold', 'shadow-sm');
      b.classList.add('bg-surface-container-low', 'text-on-surface-variant');
    });
    button.classList.remove('bg-surface-container-low', 'text-on-surface-variant');
    button.classList.add('bg-primary', 'text-on-primary', 'font-semibold', 'shadow-sm');
    activeCategory = button.getAttribute('data-category') || 'all';
    deliverables.reset();
  }));

  const modal = document.getElementById('deliverableModal');
  const modalImage = document.getElementById('modalImage');
  const modalImagePlaceholder = document.getElementById('modalImagePlaceholder');

  function openDeliverableModal(card) {
    const image = card.getAttribute('data-image') || '';
    document.getElementById('modalTitle').textContent = card.getAttribute('data-title') || '';
    document.getElementById('modalDesc').textContent = card.getAttribute('data-desc') || '';
    modalImage.classList.toggle('hidden', !image);
    modalImagePlaceholder.classList.toggle('hidden', Boolean(image));
    if (image) {
      modalImage.src = image;
      modalImage.alt = card.getAttribute('data-title') || '';
    } else {
      modalImage.removeAttribute('src');
    }
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }

  function hideDeliverableModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  }

  function closeDeliverableModal(event) {
    if (event.target.id === 'deliverableModal') hideDeliverableModal();
  }

  deliverableCards.forEach((card) => card.addEventListener('click', () => openDeliverableModal(card)));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hideDeliverableModal();
  });

  // Personnel: search + sort
  const personnelCards = Array.from(document.querySelectorAll('.personnel-card'));
  const personnelSearch = document.getElementById('personnelSearch');
  const personnelSort = document.getElementById('personnelSort');

  function numeric(card, name) {
    const raw = card.getAttribute(name);
    return raw === null || raw === '' ? null : parseFloat(raw);
  }

  const personnel = createListController({
    container: document.getElementById('personnelGrid'),
    items: personnelCards,
    perPage: 9,
    infoEl: document.getElementById('personnelInfo'),
    navEl: document.getElementById('personnelPager'),
    emptyEl: document.getElementById('personnelEmpty'),
    unit: 'Personil Terdaftar',
    matches: (card) => {
      const query = (personnelSearch?.value || '').toLowerCase().trim();
      return !query || (card.getAttribute('data-search') || '').includes(query);
    },
    compare: (a, b) => {
      const mode = personnelSort?.value || 'registry';
      if (mode === 'rank') {
        const rankA = numeric(a, 'data-rank');
        const rankB = numeric(b, 'data-rank');
        if (rankA === null && rankB === null) return 0;
        if (rankA === null) return 1;
        if (rankB === null) return -1;
        return rankB - rankA;
      }
      if (mode === 'cluster') return (a.getAttribute('data-cluster') || '').localeCompare(b.getAttribute('data-cluster') || '');
      return numeric(a, 'data-order') - numeric(b, 'data-order');
    },
  });

  personnelSearch?.addEventListener('input', () => personnel.reset());
  personnelSort?.addEventListener('change', () => personnel.reset());

  deliverables.render();
  personnel.render();
</script>






</body></html>