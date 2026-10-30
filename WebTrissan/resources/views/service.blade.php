<!DOCTYPE html><html lang="id" class="scroll-smooth" style=""><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $companyName = $globalSettings?->company_name ?? config('app.name');
    $seoTitle = $service->service_title.' | '.$companyName;
    $seoDescription = $service->metaDescription();
    $seoNoindex = ! $isIndexable;
    $phone = collect($globalSettings?->phones ?? [])->first();
    $whatsappNumber = $globalSettings?->whatsappNumber();
  @endphp
  <title>{{ $seoTitle }}</title>
  @include('layouts.seo-head', ['seoTitle' => $seoTitle, 'seoDescription' => $seoDescription, 'seoNoindex' => $seoNoindex])
  @if(filled($seoDescription))
  <meta name="description" content="{{ $seoDescription }}">
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&amp;family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #FFFFFF; color: #0A192F; }
    h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Montserrat', sans-serif; }
  </style>
@include('layouts.scale')
@vite('resources/css/site/home.css')
</head>
<body class="bg-white text-slate-800 antialiased relative overflow-x-hidden selection:bg-brand-navy selection:text-white">
  @include('layouts.navbar')

  <main class="relative">
    <!-- Hero: breadcrumb, judul, dan deskripsi layanan -->
    <section class="bg-brand-grayBg border-b border-brand-grayBorder py-10 lg:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-medium text-brand-grayMuted mb-6">
          <a href="{{ url('/') }}" class="hover:text-brand-accent transition">Beranda</a>
          <span aria-hidden="true" class="text-slate-400">/</span>
          <span aria-current="page" class="text-brand-navy font-semibold">{{ $service->short_title ?? $service->service_title }}</span>
        </nav>
        <div class="max-w-3xl">
          <span class="text-xs font-heading font-bold text-brand-accent tracking-widest uppercase">{{ $service->serviceCluster?->cluster_name }}</span>
          <h1 class="font-heading font-black text-3xl sm:text-5xl text-brand-navy tracking-tight leading-tight mt-2">{{ $service->service_title }}</h1>
          @if(filled($service->service_description))
            <p class="text-brand-grayMuted text-base sm:text-lg leading-relaxed mt-4">{{ $service->service_description }}</p>
          @endif
          <div class="flex flex-wrap items-center gap-3 mt-8">
            <a href="{{ url('/#contact-modal') }}" class="inline-flex items-center justify-center font-heading font-bold text-xs tracking-wider text-white bg-brand-navy hover:bg-brand-accent px-6 py-3.5 rounded shadow-md transition uppercase">Minta Penawaran</a>
            @if($works->isNotEmpty())
              <a href="#proyek" class="inline-flex items-center justify-center font-heading font-bold text-xs tracking-wider text-brand-navy bg-white border border-brand-grayBorder hover:border-brand-accent px-6 py-3.5 rounded transition uppercase">Lihat Proyek</a>
            @endif
          </div>
        </div>
      </div>
    </section>

    <!-- Uraian layanan -->
    @if(! empty($service->landing_paragraphs))
    <section class="py-10 lg:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <div class="max-w-3xl">
          <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight mb-6">Tentang Layanan {{ $service->short_title ?? $service->service_title }}</h2>
          <div class="space-y-4 text-slate-700 text-base leading-relaxed">
            @foreach($service->landing_paragraphs as $paragraph)
              <p>{{ $paragraph }}</p>
            @endforeach
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Tahapan pengerjaan -->
    @if(! empty($service->landing_steps))
    <section class="py-10 lg:py-16 bg-brand-grayBg border-t border-b border-brand-grayBorder">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight mb-8">Tahapan Pengerjaan</h2>
        <ol class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
          @foreach($service->landing_steps as $step)
            <li class="bg-white rounded-lg border border-brand-grayBorder shadow-sm p-5 lg:p-6">
              <span class="inline-flex items-center justify-center w-9 h-9 rounded bg-brand-navy text-white font-heading font-bold text-sm mb-4">{{ $loop->iteration }}</span>
              <h3 class="font-heading font-bold text-lg text-brand-navy tracking-tight mb-2">{{ $step['title'] }}</h3>
              @if(filled($step['description']))
                <p class="text-sm text-brand-grayMuted leading-relaxed">{{ $step['description'] }}</p>
              @endif
            </li>
          @endforeach
        </ol>
      </div>
    </section>
    @endif

    <!-- Proyek terkait dari CMS -->
    @if($works->isNotEmpty())
    <section id="proyek" class="py-10 lg:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight mb-8">Proyek {{ $service->short_title ?? $service->service_title }} yang Telah Kami Kerjakan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
          @foreach($works as $work)
            <a href="{{ url('/works#'.($work->work_slug ?? '')) }}" class="group bg-white rounded-lg border border-brand-grayBorder overflow-hidden shadow-sm hover:shadow-card-hover transition flex flex-col">
              <div class="h-44 lg:h-52 bg-slate-900 overflow-hidden">
                @if($work->image_url)
                  <img src="{{ $work->image_url }}" alt="{{ $work->work_title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @endif
              </div>
              <div class="p-5 flex-1">
                @if(filled($work->client_sector))
                  <span class="text-[11px] font-mono font-semibold text-brand-accent">{{ $work->client_sector }}</span>
                @endif
                <h3 class="font-heading font-bold text-base text-brand-navy group-hover:text-brand-accent transition tracking-tight leading-snug mt-1">{{ $work->work_title }}</h3>
                @if(filled($work->short_description))
                  <p class="text-xs text-brand-grayMuted mt-2 leading-relaxed">{{ $work->short_description }}</p>
                @endif
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    <!-- FAQ -->
    @if(! empty($service->landing_faqs))
    <section class="py-10 lg:py-16 bg-brand-grayBg border-t border-b border-brand-grayBorder">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <div class="max-w-3xl">
          <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight mb-6">Pertanyaan yang Sering Diajukan</h2>
          <div class="space-y-3">
            @foreach($service->landing_faqs as $faq)
              <details class="group bg-white rounded-lg border border-brand-grayBorder shadow-sm p-4 lg:p-5">
                <summary class="cursor-pointer list-none font-heading font-bold text-sm sm:text-base text-brand-navy flex items-center justify-between gap-4">
                  <span>{{ $faq['question'] }}</span>
                  <span aria-hidden="true" class="text-brand-accent transition group-open:rotate-45 text-xl leading-none">+</span>
                </summary>
                <p class="text-sm text-brand-grayMuted leading-relaxed mt-3">{{ $faq['answer'] }}</p>
              </details>
            @endforeach
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Ajakan menghubungi -->
    <section id="hubungi" class="py-10 lg:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <div class="bg-brand-navy text-white rounded-lg p-6 sm:p-10 lg:p-12 shadow-heavy border-t-4 border-brand-accent">
          <h2 class="font-heading font-extrabold text-2xl sm:text-3xl tracking-tight">Diskusikan Kebutuhan {{ $service->short_title ?? $service->service_title }} Anda</h2>
          <p class="text-slate-300 text-sm leading-relaxed mt-3 max-w-2xl">Ceritakan kebutuhan proyek Anda. Tim kami akan menghubungi Anda untuk membahas ruang lingkup pekerjaan dan penawaran.</p>
          <div class="flex flex-wrap items-center gap-3 mt-8">
            <a href="{{ url('/#contact-modal') }}" class="inline-flex items-center justify-center font-heading font-bold text-xs tracking-wider text-brand-navy bg-white hover:bg-slate-100 px-6 py-3.5 rounded shadow-md transition uppercase">Kirim Pesan</a>
            @if(filled($phone['tel_url'] ?? null))
              <a href="{{ $phone['tel_url'] }}" class="inline-flex items-center justify-center font-heading font-bold text-xs tracking-wider text-white border border-white/30 hover:bg-white/10 px-6 py-3.5 rounded transition">{{ $phone['display_text'] ?? 'Telepon' }}</a>
            @endif
            @if(filled($whatsappNumber))
              <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center font-heading font-bold text-xs tracking-wider text-white border border-white/30 hover:bg-white/10 px-6 py-3.5 rounded transition uppercase">WhatsApp</a>
            @endif
          </div>
        </div>
      </div>
    </section>

    <!-- Layanan lain dalam kluster yang sama -->
    @if($relatedServices->isNotEmpty())
    <section class="pb-10 lg:pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
        <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-brand-navy tracking-tight mb-5">Layanan Terkait</h2>
        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach($relatedServices as $related)
            <li>
              <a href="{{ $related->publicUrl() }}" class="block bg-white rounded-lg border border-brand-grayBorder p-4 hover:border-brand-accent transition">
                <span class="font-heading font-bold text-sm text-brand-navy">{{ $related->short_title ?? $related->service_title }}</span>
                @if(filled($related->service_description))
                  <span class="block text-xs text-brand-grayMuted mt-1 line-clamp-2">{{ $related->service_description }}</span>
                @endif
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </section>
    @endif
  </main>

  @include('layouts.footer')
</body></html>
