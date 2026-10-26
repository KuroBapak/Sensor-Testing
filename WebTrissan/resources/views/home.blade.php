<!DOCTYPE html><html lang="id" class="scroll-smooth" style=""><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pageSettings->seo_title ?? 'Apex Industrial Engineering - Modern Industrial Solutions' }}</title>
  @if(!empty($pageSettings->seo_description))
  <meta name="description" content="{{ $pageSettings->seo_description }}">
  @endif
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Montserrat & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&amp;family=Inter:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#0A192F',
              navyDark: '#060D17',
              navyLight: '#132845',
              steel: '#25446B',
              accent: '#0066CC',
              grayBg: '#F8FAFC',
              grayBorder: '#E2E8F0',
              grayMuted: '#64748B'
            }
          },
          fontFamily: {
            heading: ['Montserrat', 'sans-serif'],
            body: ['Inter', 'sans-serif']
          },
          boxShadow: {
            'industrial': '0 20px 40px -15px rgba(10, 25, 47, 0.08), 0 0 1px 1px rgba(10, 25, 47, 0.05)',
            'card-hover': '0 25px 50px -12px rgba(10, 25, 47, 0.18)',
            'heavy': '0 30px 60px -20px rgba(6, 13, 23, 0.35)'
          }
        }
      }
    }
  </script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #FFFFFF;
      color: #0A192F;
    }
    h1, h2, h3, h4, h5, h6, .font-heading {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-slate-800 antialiased relative overflow-x-hidden selection:bg-brand-navy selection:text-white">

  @include('layouts.navbar')

  <main class="relative">

    <!-- 2. Bagian Landing Page (Hero) -->
    <section id="landing" class="relative w-full min-h-[560px] lg:min-h-[680px] bg-brand-navy flex items-center overflow-hidden">
            <!-- Background Gambar Fasilitas Pabrik Penuh dengan Overlay Gelap -->
      <div class="absolute inset-0 z-0 overflow-hidden" id="hero-carousel-container">
        @php
          $heroSlides = $pageSettings->hero_slides ?? [];
          $defaultSlides = [
            ['image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDahTpOLFtiP0cjbaka1P0zz7EoMcQwxrIYK0EtOoGRIqIsgUXFV562tq74x2BhW2fRn3L5ex-Gwgm-opu9oWfcSKaclPyHG-r5UGWy6qS0nIFvSG8RaO_RUJw0IL4pXc2jfdr-KBNvRPv5kEdvw_V0WYsJWUxtqj-ScoWtJ72OZzUVMLOC6xVVbZGMYNQsW994_YDN1mWd5qJaV2lhAAg-0TcFJIria7iBoCcfrAAgBioboS22Xgew', 'alt' => 'Manufacturing Facility'],
            ['image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCYLbne5xVfziHtl9ouQePMOql-0lVZ6W1lydOHuy1ForFKi60xT0IRaUlxALs6aHz3ToC6MEHu748HwGOXVkA-hAKvh3wB6AdWg7L_mMJuSY3bZqjtv3CNIxo0fUC2Ff0al6jKmuo8MtQL1PWpn8Zgl6YcnPcG1KA4ejAx3iciLPc_DZgSDxUXaGSzx88Dzvu2CQy4LLu_CBAQXCSZjJfIccYW_fAA59ESQoExTReRrmMIqH-c2UyA', 'alt' => 'Precision Machining'],
            ['image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDw3qQEbwvTdSrL1ZkQB2F1D4CGu5VYgB1wZn3FFKzpigBSfbCBbONLBu5smdG5LgESwEDldsFLO0EWzndddNRHSj7S9RVNLKw0NN67q8pAFcnYLT37BAFuGp7hZuSMvoq8vdsv2FhNYotT-l117pLCGmDf6YZlngeDZuVrGZmRLYQAaxBaY9p9lNfJzuwqdHbyqMvlQUEwyE0CW5-RlYAu4P1YLjFp7RotaMbG2SHQspbMGcxAxxJq', 'alt' => 'Automation Assembly'],
          ];
        @endphp
        @if(!empty($heroSlides) && is_array($heroSlides))
          @foreach($heroSlides as $index => $slide)
            @php
              $slideImg = \App\Support\MediaUrl::resolve($slide['image'] ?? null)
                ?? ($defaultSlides[$index % count($defaultSlides)]['image'] ?? $defaultSlides[0]['image']);
              $slideAlt = $slide['alt_text'] ?? ($slide['alt'] ?? ($slide['caption'] ?? ($defaultSlides[$index % count($defaultSlides)]['alt'] ?? 'Manufacturing Facility')));
            @endphp
            <div class="hero-slide absolute inset-0 transition-opacity duration-1000 {{ $loop->first ? 'opacity-100' : 'opacity-0' }}">
              <img src="{{ $slideImg }}" alt="{{ $slideAlt }}" class="w-full h-full object-cover object-center transform scale-105">
              <div class="absolute inset-0 bg-gradient-to-r from-brand-navyDark/95 via-brand-navy/85 to-brand-navyDark/65"></div>
              <div class="absolute inset-0 bg-brand-navy/40 mix-blend-multiply"></div>
            </div>
          @endforeach
        @else
          @foreach($defaultSlides as $index => $slide)
            <div class="hero-slide absolute inset-0 transition-opacity duration-1000 {{ $loop->first ? 'opacity-100' : 'opacity-0' }}">
              <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}" class="w-full h-full object-cover object-center transform scale-105">
              <div class="absolute inset-0 bg-gradient-to-r from-brand-navyDark/95 via-brand-navy/85 to-brand-navyDark/65"></div>
              <div class="absolute inset-0 bg-brand-navy/40 mix-blend-multiply"></div>
            </div>
          @endforeach
        @endif
      </div>

      <!-- Konten Hero di Sisi Kiri -->
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 w-full pt-10 pb-32 lg:pb-24 lg:pt-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-10">
          <div class="max-w-2xl text-left">
            <h1 class="font-heading font-black text-3xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.12] mb-4 lg:mb-6 drop-shadow-md mt-1">{{ $pageSettings->hero_title ?? 'Dedikasi Presisi Manufaktur Mutakhir' }}</h1>
            <p class="text-slate-300 text-sm sm:text-lg leading-relaxed font-normal max-w-xl mb-6 lg:mb-10">{{ $pageSettings->hero_subtitle ?? 'Menghadirkan rekayasa komponen mekanikal bertoleransi tinggi serta integrasi otomasi industri berstandar mutu internasional.' }}</p>
            <div class="flex items-center gap-4">
              <a href="{{ $pageSettings->hero_cta_url ?? '#works' }}" class="inline-flex items-center justify-center font-heading font-bold text-xs sm:text-sm tracking-wider text-brand-navy bg-white hover:bg-slate-100 px-8 py-4 rounded shadow-heavy hover:shadow-2xl transition duration-200 transform hover:-translate-y-0.5 active:translate-y-0 uppercase group">
                <span>{{ $pageSettings->hero_cta_text ?? 'Lihat Portofolio' }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-4 h-4 ml-2 group-hover:translate-x-1.5 transition"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
              </a>
            </div>
          </div>
          <div class="w-full lg:w-80 shrink-0 self-end lg:self-center">
            @php
              $trustCard = $pageSettings->trust_card ?? [];
              $trustBadge = $trustCard['label'] ?? ($trustCard['badge'] ?? 'TRUSTED BY 50+ LEADERS');
              $trustRating = $trustCard['rating'] ?? '★ 4.9/5.0';
              $trustDescription = $trustCard['description'] ?? 'Global tier-1 original equipment manufacturers & defense contractors trust our precision standards.';
              $trustClients = $trustCard['clients'] ?? ['SIEMENS', 'BOEING', 'CAT', 'KOMATSU'];
              $trustFooterLeft = $trustCard['footer_left'] ?? 'AS9100D & ISO 9001';
              $trustFooterRight = $trustCard['footer_right'] ?? 'Global Audited';
            @endphp
            <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-lg p-5 shadow-heavy text-white">
              <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-3.5">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span class="text-[10px] font-heading font-bold tracking-widest uppercase text-blue-300">{{ $trustBadge }}</span>
                </div>
                <span class="text-[10px] font-semibold text-slate-300 bg-white/10 px-2 py-0.5 rounded border border-white/10">{{ $trustRating }}</span>
              </div>
              <p class="text-xs text-slate-300 mb-4 font-normal leading-relaxed">{{ $trustDescription }}</p>
              <div class="grid grid-cols-2 gap-2 text-xs font-heading font-bold tracking-wider text-slate-200">
                @if(is_array($trustClients))
                  @foreach($trustClients as $client)
                    <div class="px-2.5 py-1.5 rounded bg-white/5 border border-white/10 flex items-center gap-1.5 hover:bg-white/10 transition">
                      <span class="text-blue-400 font-black">•</span> {{ is_array($client) ? ($client['name'] ?? ($client['client'] ?? '')) : $client }}
                    </div>
                  @endforeach
                @endif
              </div>
              <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-300">
                <span class="text-blue-300 font-semibold">{{ $trustFooterLeft }}</span>
                <span class="text-slate-400">{{ $trustFooterRight }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Bagian Services (Kategori Pekerjaan) -->
    <!-- Menindih bagian bawah gambar hero -->
    <section id="services" class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 -mt-24 lg:-mt-28"><div class="relative group/carousel"><div class="flex items-center justify-between mb-4 md:hidden"><span class="text-xs font-heading font-bold text-slate-400 uppercase tracking-widest">Core Capabilities ({{ $services->count() }} Services)</span><div class="flex items-center gap-2"><button onclick="document.getElementById('services-slider').scrollBy({left: -320, behavior: 'smooth'})" aria-label="Previous" class="w-9 h-9 rounded bg-white border border-brand-grayBorder flex items-center justify-center text-brand-navy hover:bg-brand-navy hover:text-white transition shadow-sm cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg></button><button onclick="document.getElementById('services-slider').scrollBy({left: 320, behavior: 'smooth'})" aria-label="Next" class="w-9 h-9 rounded bg-white border border-brand-grayBorder flex items-center justify-center text-brand-navy hover:bg-brand-navy hover:text-white transition shadow-sm cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg></button></div></div><button onclick="document.getElementById('services-slider').scrollBy({left: -380, behavior: 'smooth'})" aria-label="Previous Slide" class="hidden md:flex absolute -left-5 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-lg bg-white/95 backdrop-blur-md border border-brand-grayBorder text-brand-navy hover:text-white hover:bg-brand-navy items-center justify-center shadow-heavy hover:shadow-card-hover transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg></button><button onclick="document.getElementById('services-slider').scrollBy({left: 380, behavior: 'smooth'})" aria-label="Next Slide" class="hidden md:flex absolute -right-5 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-lg bg-white/95 backdrop-blur-md border border-brand-grayBorder text-brand-navy hover:text-white hover:bg-brand-navy items-center justify-center shadow-heavy hover:shadow-card-hover transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg></button><div id="services-slider" class="flex gap-6 lg:gap-8 overflow-x-auto scroll-smooth pb-4 pt-1 snap-x select-none">
          @forelse($services as $service)
            <div onclick="window.location.href='{{ url('/works?category=' . ($service->slug ?? '')) }}'" class="snap-start shrink-0 w-[88vw] sm:w-[360px] lg:w-[380px] group cursor-pointer bg-white rounded-lg p-8 border border-brand-grayBorder shadow-industrial hover:shadow-card-hover hover:border-brand-accent transition-all duration-300 transform hover:-translate-y-2 flex flex-col justify-between relative overflow-hidden">
              <div class="absolute top-0 left-0 w-full h-1.5 bg-brand-navy group-hover:bg-brand-accent transition"></div>
              <div>
                <div class="w-14 h-14 rounded-lg bg-brand-grayBg border border-slate-200 flex items-center justify-center text-brand-navy group-hover:bg-brand-navy group-hover:text-white transition duration-300 mb-6 shadow-sm">
                  @if(!empty($service->service_icon_svg))
                    {!! $service->service_icon_svg !!}
                  @elseif(!empty($service->service_icon))
                    <span class="material-symbols-outlined text-3xl">{{ $service->service_icon }}</span>
                  @else
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                  @endif
                </div>
                <h3 class="font-heading font-bold text-xl text-brand-navy tracking-tight mb-3 group-hover:text-brand-accent transition">{{ $service->short_title ?? $service->service_title }}</h3>
                <p class="text-brand-grayMuted text-sm leading-relaxed mb-6 font-normal">{{ $service->service_description }}</p>
              </div>
              <div class="flex items-center text-xs font-heading font-bold tracking-wider text-brand-navy group-hover:text-brand-accent uppercase mt-2">
                <span>EXPLORE WORKS</span>
                <svg class="w-4 h-4 ml-1.5 transform group-hover:translate-x-1.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6"></path></svg>
              </div>
            </div>
          @empty
            <div class="text-center py-8 text-slate-500 w-full">Belum ada data layanan.</div>
          @endforelse
        </div></div></section>

    <!-- AREA RELATIVE CONTAINER DENGAN ROBOTIC ARM MELAYANG DI SISI KANAN SEKITAR SERVICES DAN WORKS -->
    <div class="relative">
      
      <!-- Ilustrasi 3D Lengan Robotik (Robotic Arm) yang melayang di sisi kanan membentang di sekitar Services dan Works -->
      

      <!-- 4. Bagian Works (Hasil Pekerjaan dengan Tab Filter Interaktif) -->
      <section id="about" class="py-12 lg:py-20 bg-brand-grayBg border-t border-brand-grayBorder relative overflow-hidden">
        @php
          $aboutTeaser = $pageSettings->about_teaser ?? [];
          $aboutImage = \App\Support\MediaUrl::resolve($aboutTeaser['image'] ?? null) ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuC7D7HAstdKsEhQ5jP4KgjDD06L8XEAneixTri3F8rqZCOFH38u54XUTBd6kxGV-jmWQWfz2rPnnGT9jVfS1ixR2LLUonFiB_qXPn4Vj93pWlc1SvJGb7kqiYBFS-rnSB541Azad48DmqUtAnzK2dl9Ku27bRS_zSEPLk6dgs9g4l6SVWMn5l4INGfVwH5Sndf5bWVsk4qerFO0bXLeLns3x1RtkpfNhS5VPZTcLqxV1BmhvSlZZ9CL';
          $aboutBadge = $aboutTeaser['image_badge'] ?? 'EST. 1998 • 25+ THN';
          $aboutEyebrow = $aboutTeaser['eyebrow'] ?? 'TENTANG '.strtoupper($globalSettings?->company_name ?? 'KAMI');
          $aboutTitle = $aboutTeaser['title'] ?? 'Dedikasi Rekayasa Presisi & Standar Manufaktur Kelas Dunia';
          $aboutDescription = $aboutTeaser['description']
            ?? (isset($aboutTeaser['paragraph_1']) || isset($aboutTeaser['paragraph_2'])
                ? trim(($aboutTeaser['paragraph_1'] ?? '') . "\n\n" . ($aboutTeaser['paragraph_2'] ?? ''))
                : "Sebagai mitra rekayasa industri tier-1, Apex Industrial Engineering memadukan kecanggihan teknologi permesinan CNC 5-sumbu mutakhir, fabrikasi metalurgi berakurasi tinggi, dan integrasi mekatronika otomatis untuk sektor-sektor kritis seperti kedirgantaraan, pertahanan, otomotif presisi tinggi, dan infrastruktur maritim.\n\nDengan komitmen tanpa henti pada kepatuhan toleransi mikro hingga 0.002mm serta audit mutu menyeluruh, kami membantu memitigasi risiko manufaktur dan mempercepat siklus peluncuran sistem industri bagi konglomerasi global terdepan.");
          $aboutCtaText = $aboutTeaser['cta_text'] ?? 'Pelajari Profil Lengkap';
          $aboutCtaUrl = $aboutTeaser['cta_url'] ?? '/about';

          $companyStats = collect($globalSettings?->company_stats ?? [])
            ->map(fn ($companyStat) => ['value' => $companyStat['value'] ?? '', 'label' => $companyStat['label_landing'] ?? ($companyStat['label_about'] ?? '')])
            ->all();
          $stats = $companyStats ?: $pageSettings->stats ?? [
            ['value' => '25+', 'label' => 'Tahun Pengalaman', 'color' => 'text-brand-navy'],
            ['value' => '99.98%', 'label' => 'Presisi Toleransi', 'color' => 'text-brand-accent'],
            ['value' => '150+', 'label' => 'Insinyur Ahli', 'color' => 'text-brand-navy'],
            ['value' => '500+', 'label' => 'Proyek Global', 'color' => 'text-brand-navy'],
          ];
        @endphp
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            <div class="lg:col-span-6 relative">
              <div class="relative rounded-lg overflow-hidden border border-brand-grayBorder shadow-heavy bg-slate-900 group">
                <img src="{{ $aboutImage }}" alt="{{ $aboutTitle }}" class="w-full h-[460px] lg:h-[520px] object-cover object-center group-hover:scale-105 transition duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-navyDark/85 via-transparent to-transparent"></div>
                <div class="absolute bottom-4 left-4 inline-flex items-center gap-2 bg-brand-navy/90 backdrop-blur-md border border-white/15 px-3 py-1.5 rounded-lg text-white shadow-heavy z-10">
                  <span class="text-[11px] font-heading font-bold text-slate-200 uppercase tracking-widest">{{ $aboutBadge }}</span>
                </div>
              </div>
            </div>
            <div class="lg:col-span-6 flex flex-col justify-center">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-white border border-brand-grayBorder text-brand-navy text-xs font-semibold tracking-wider uppercase mb-4 self-start shadow-sm">
                <span class="w-2 h-2 rounded-full bg-brand-accent"></span>{{ $aboutEyebrow }}
              </div>
              <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-navy tracking-tight leading-tight mb-6">{{ $aboutTitle }}</h2>
              @php
                $aboutParagraphs = array_values(array_filter(array_map('trim', explode("\n", (string) $aboutDescription))));
              @endphp
              @if(!empty($aboutParagraphs))
                <div class="space-y-4 mb-8">
                  @foreach($aboutParagraphs as $paragraph)
                    <p class="text-brand-grayMuted text-sm leading-relaxed font-normal">{{ $paragraph }}</p>
                  @endforeach
                </div>
              @endif
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8 pt-6 border-t border-slate-200/80">
                @foreach($stats as $index => $stat)
                  @php
                    $val = is_array($stat) ? ($stat['value'] ?? ($stat['stat_value'] ?? '')) : $stat;
                    $lbl = is_array($stat) ? ($stat['label'] ?? ($stat['stat_label'] ?? '')) : '';
                    $col = is_array($stat) ? ($stat['color'] ?? ($index === 1 ? 'text-brand-accent' : 'text-brand-navy')) : 'text-brand-navy';
                  @endphp
                  <div class="p-3.5 rounded-lg bg-white border border-slate-200 shadow-sm">
                    <span class="font-heading font-extrabold text-2xl {{ $col }} block">{{ $val }}</span>
                    <span class="text-[11px] text-brand-grayMuted font-medium tracking-wide uppercase mt-1 block">{{ $lbl }}</span>
                  </div>
                @endforeach
              </div>
              <div class="flex items-center gap-4">
                <a href="{{ $aboutCtaUrl }}" class="inline-flex items-center font-heading font-bold text-xs tracking-wider text-brand-navy hover:text-brand-accent uppercase transition group">
                  <span>{{ $aboutCtaText }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right w-4 h-4 ml-2 transform group-hover:translate-x-1.5 transition"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="works" class="bg-white border-t border-b border-brand-grayBorder relative py-12 lg:py-20"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12"><div class="max-w-xl mb-10"><div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-brand-grayBg border border-brand-grayBorder text-brand-navy text-xs font-semibold tracking-wider uppercase mb-3 shadow-sm"><span class="w-2 h-2 rounded-full bg-brand-accent"></span>ENGINEERED OUTPUT</div><h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-navy tracking-tight mt-1">Featured Industrial Works</h2><p class="text-brand-grayMuted text-sm mt-3 leading-relaxed">Tinjau contoh rekam jejak presisi manufaktur dan standar mutu pabrik kami yang mematuhi standar toleransi mikro ketat.</p></div><div id="works-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($featuredWorks as $work)
          @php
            $workImg = $work->image_url ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuCYLbne5xVfziHtl9ouQePMOql-0lVZ6W1lydOHuy1ForFKi60xT0IRaUlxALs6aHz3ToC6MEHu748HwGOXVkA-hAKvh3wB6AdWg7L_mMJuSY3bZqjtv3CNIxo0fUC2Ff0al6jKmuo8MtQL1PWpn8Zgl6YcnPcG1KA4ejAx3iciLPc_DZgSDxUXaGSzx88Dzvu2CQy4LLu_CBAQXCSZjJfIccYW_fAA59ESQoExTReRrmMIqH-c2UyA';
            $categorySlug = $work->service?->slug ?? 'general';
          @endphp
          <div class="work-card {{ $categorySlug }} bg-white rounded-lg border border-brand-grayBorder overflow-hidden shadow-sm hover:shadow-card-hover transition group flex flex-col justify-between cursor-pointer" onclick="window.location.href='{{ url('/works#' . ($work->work_slug ?? '')) }}'">
            <div class="h-52 bg-slate-900 overflow-hidden relative">
              <img src="{{ $workImg }}" alt="{{ $work->work_title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
              <div class="absolute inset-0 bg-gradient-to-t from-brand-navyDark/85 via-transparent to-transparent"></div>
              <div class="absolute top-3 left-3 px-2.5 py-1 bg-brand-navy/90 backdrop-blur-md text-white font-mono text-[10px] font-bold tracking-wider uppercase rounded border border-white/10 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> {{ $work->top_badge ?? ($work->service?->short_title ?? $work->service?->service_title ?? 'INDUSTRIAL WORK') }}
              </div>
              @if(!empty($work->bottom_badge))
                <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded bg-black/60 backdrop-blur-md text-white text-[10px] font-mono border border-white/10">{{ $work->bottom_badge }}</span>
              @endif
            </div>
            <div class="p-6 flex-1 flex flex-col justify-between">
              <div class="mb-4">
                <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 mb-1.5">
                  <span>{{ $work->serial_number ?? 'SERIES-TR-' . str_pad($work->id, 4, '0', STR_PAD_LEFT) }}</span>
                  <span class="text-brand-accent font-semibold">{{ $work->client_sector ?? 'INDUSTRIAL TIER-1' }}</span>
                </div>
                <h4 class="font-heading font-bold text-lg text-brand-navy group-hover:text-brand-accent transition tracking-tight">{{ $work->work_title }}</h4>
                <p class="text-xs text-brand-grayMuted mt-2 leading-relaxed">{{ $work->short_description }}</p>
              </div>
              <div>
                <div class="flex flex-wrap gap-1.5 mb-5">
                  @if(!empty($work->highlight_tags) && is_array($work->highlight_tags))
                    @foreach($work->highlight_tags as $idx => $tag)
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-medium {{ $idx === 0 ? 'bg-blue-500/10 text-brand-accent border border-blue-500/20' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">{{ $tag }}</span>
                    @endforeach
                  @elseif(!empty($work->material))
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-blue-500/10 text-brand-accent border border-blue-500/20">{{ $work->material }}</span>
                  @endif
                </div>
                <a href="{{ url('/works#' . ($work->work_slug ?? '')) }}" class="inline-flex items-center justify-between w-full px-4 py-2.5 rounded bg-blue-500/10 hover:bg-brand-navy text-brand-accent hover:text-white border border-blue-500/20 hover:border-brand-navy text-xs font-heading font-bold uppercase tracking-wider transition-all duration-200 group/btn">
                  <span>Lihat Spesifikasi &amp; Detail</span>
                  <svg class="w-4 h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-span-full text-center py-12 text-slate-500">Belum ada portofolio proyek unggulan.</div>
        @endforelse
      </div><div class="mt-12 text-center flex flex-col sm:flex-row items-center justify-center gap-4"><a href="/works" class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded bg-brand-navy text-white hover:bg-brand-accent font-heading font-bold text-xs tracking-wider uppercase transition-all duration-300 shadow-md hover:shadow-card-hover group"><span class="">Lihat Semua Portofolio Proyek (Works)</span><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transform group-hover:translate-x-1.5 transition"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a></div></div></section>

    </div>

    <!-- 5. Bagian Portfolio (Galeri Proses Manufaktur & Peralatan dengan Tombol Biru Persegi Panjang) -->
    <section id="services-overview" class="bg-brand-grayBg border-t border-b border-brand-grayBorder relative py-12 lg:py-20"><div class="max-w-7xl mx-auto px-6 lg:px-12"><div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6"><div class="max-w-2xl"><div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-white border border-brand-grayBorder text-brand-navy text-xs font-semibold tracking-wider uppercase mb-3 shadow-sm"><span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>LAYANAN &amp; SPESIALISASI TEKNIK / CAPABILITY DIRECTORY</div><h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-navy tracking-tight mt-1">Layanan &amp; Kapabilitas Rekayasa Industri</h2><p class="text-brand-grayMuted text-sm mt-3 leading-relaxed">Direktori lengkap kapabilitas rekayasa mekanikal, automasi, kelistrikan, dan pengadaan industri terpadu. Klik setiap layanan spesialisasi untuk meninjau dokumentasi proyek pada rekam jejak pekerjaan kami.</p></div><div class="shrink-0"><a href="/works" class="inline-flex items-center gap-2 px-5 py-2.5 rounded bg-brand-navy text-white hover:bg-brand-accent text-xs font-heading font-bold tracking-wider uppercase transition shadow-sm"><span class="">Eksplorasi Semua Services</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a></div></div><div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @forelse($serviceClusters as $cluster)
          <div class="bg-white rounded-lg border border-brand-grayBorder shadow-industrial hover:shadow-card-hover transition-all duration-300 overflow-hidden flex flex-col">
            <div class="p-6 bg-slate-50 border-b border-brand-grayBorder flex items-center justify-between">
              <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-lg bg-brand-navy text-white flex items-center justify-center font-mono font-bold text-sm shadow-sm">
                  @if(!empty($cluster->cluster_icon_svg))
                    {!! $cluster->cluster_icon_svg !!}
                  @elseif(!empty($cluster->cluster_icon))
                    <span class="material-symbols-outlined text-xl text-blue-300">{{ $cluster->cluster_icon }}</span>
                  @else
                    <span class="material-symbols-outlined text-xl text-blue-300">precision_manufacturing</span>
                  @endif
                </div>
                <div>
                  <span class="text-[11px] font-mono font-bold tracking-widest text-brand-accent uppercase block">{{ $cluster->cluster_tag ?? ('CLUSTER ' . str_pad($cluster->sort_order ?? $loop->iteration, 2, '0', STR_PAD_LEFT)) }}</span>
                  <h3 class="font-heading font-bold text-lg text-brand-navy tracking-tight">{{ $cluster->cluster_name }}</h3>
                </div>
              </div>
              <span class="text-xs font-mono font-semibold px-2.5 py-1 rounded bg-white border border-slate-200 text-slate-600 whitespace-nowrap shrink-0">{{ $cluster->services->count() }} Layanan</span>
            </div>
            <div class="p-2 flex-1 flex flex-col divide-y divide-slate-100">
              @foreach($cluster->services as $svc)
                <a href="{{ url('/works?category=' . ($svc->slug ?? '')) }}" class="group flex items-center justify-between p-3.5 rounded hover:bg-slate-50 transition">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded bg-slate-100 text-brand-navy flex items-center justify-center shrink-0 group-hover:bg-brand-navy group-hover:text-white transition">
                      @if(!empty($svc->service_icon_svg))
                        {!! $svc->service_icon_svg !!}
                      @elseif(!empty($svc->service_icon))
                        <span class="material-symbols-outlined text-base">{{ $svc->service_icon }}</span>
                      @else
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                      @endif
                    </div>
                    <div>
                      <h4 class="font-heading font-bold text-sm text-brand-navy group-hover:text-brand-accent transition">{{ $svc->short_title ?? $svc->service_title }}</h4>
                      <p class="text-xs text-brand-grayMuted line-clamp-1">{{ $svc->service_description }}</p>
                    </div>
                  </div>
                  <div class="text-slate-400 group-hover:text-brand-accent transition ml-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transform group-hover:translate-x-1 transition"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        @empty
          <div class="col-span-full text-center py-12 text-slate-500">Belum ada direktori kapabilitas layanan.</div>
        @endforelse
      </div></div></section>

    <!-- 6. Bagian Contact (Blok Biru Dongker + Area Mission/Values/Motto + Deretan Logo Klien + 3D Roda Gigi Metalik di Sudut Kanan Bawah) -->
    <section id="contact" class="border-t border-brand-grayBorder relative overflow-hidden py-12 lg:py-20 bg-white">
      @php
        $httpsOnly = fn (?string $url, string $fallback): string => \Illuminate\Support\Str::startsWith((string) $url, 'https://') ? $url : $fallback;
        $mapsButtonUrl = $httpsOnly($globalSettings?->maps_button_url, 'https://maps.google.com/?q=Kawasan+Industri+Jababeka+V+Cikarang');
        $mapsEmbedUrl = $httpsOnly($globalSettings?->maps_iframe_embed_url, 'https://maps.google.com/maps?q=-6.315024,107.151817&hl=id&z=15&output=embed');
      @endphp
      
      <!-- GAMBAR 3D RODA GIGI (GEAR) METALIK TAJAM DI SUDUT KANAN BAWAH HALAMAN -->
      

      <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-20">
        
        <!-- Grid: Blok Persegi Panjang Tebal Biru Dongker & Area Bersih Mission, Values and Motto -->
        <div class="text-center max-w-2xl mx-auto mb-12"><div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-white border border-brand-grayBorder text-brand-navy text-xs font-semibold tracking-wider uppercase mb-3"><span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>GET IN TOUCH</div><h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-brand-navy tracking-tight">Contact Us</h2><p class="text-brand-grayMuted text-sm mt-3 leading-relaxed">Hubungi tim rekayasa industri kami untuk konsultasi teknis, kemitraan proyek manufaktur, dan permintaan penawaran (RFQ).</p></div><div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-stretch mb-12">
          
          <!-- Blok Persegi Panjang Tebal dengan Latar Belakang Biru Dongker -->
          <div class="lg:col-span-6 bg-brand-navy text-white rounded-lg p-6 sm:p-10 lg:p-12 shadow-heavy flex flex-col justify-between border-t-4 border-brand-accent relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-brand-steel/20 rounded-full blur-2xl pointer-events-none"></div>
            <div>
              <span class="text-xs font-heading font-bold text-blue-300 tracking-widest uppercase">Direct Inquiries</span>
              <h3 class="font-heading font-extrabold text-2xl sm:text-4xl text-white tracking-tight mt-1 mb-6">
                How Can We Help?
              </h3>
              <p class="text-slate-300 text-sm leading-relaxed mb-8">
                Konsultasikan kebutuhan permesinan, fabrikasi pengelasan bersertifikat, atau proyek integrasi mekanikal Anda dengan tim insinyur senior kami.
              </p>

              <!-- Detail Nomor Telepon & Alamat -->
              <div class="space-y-5 text-sm">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded bg-white/10 flex items-center justify-center text-blue-400 shrink-0 border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="phone-call" aria-hidden="true" class="lucide lucide-phone-call w-5 h-5"><path d="M13 2a9 9 0 0 1 9 9"></path><path d="M13 6a5 5 0 0 1 5 5"></path><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"></path></svg>
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Telepon Hotline</span>
                    @forelse(($globalSettings?->phones ?? []) as $phone)
                      <a href="{{ $phone['tel_url'] ?? '#' }}" class="font-heading font-bold text-white text-base hover:text-blue-300 transition">{{ $phone['display_text'] ?? '' }}</a>@if(!$loop->last) <span class="text-slate-400">/</span> @endif
                    @empty
                      <span class="font-heading font-bold text-white text-base">-</span>
                    @endforelse
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded bg-white/10 flex items-center justify-center text-blue-400 shrink-0 border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" aria-hidden="true" class="lucide lucide-map-pin w-5 h-5"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Alamat Pabrik &amp; Fasilitas</span>
                    <p class="font-medium text-slate-200 text-sm mt-0.5">{{ $globalSettings?->factory_address }}</p>
                  </div>
                </div>
              <div class="flex items-start gap-4"><div class="w-10 h-10 rounded bg-white/10 flex items-center justify-center text-blue-400 shrink-0 border border-white/10"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"></path></svg></div><div><span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">SURAT ELEKTRONIK (EMAIL)</span><a href="mailto:{{ $globalSettings?->primary_email }}" class="font-heading font-bold text-white text-base hover:text-blue-300 transition block">{{ $globalSettings?->primary_email }}</a>@if($globalSettings?->corporate_email)<span class="text-xs text-slate-300 font-medium block mt-0.5"><a href="mailto:{{ $globalSettings->corporate_email }}" class="text-blue-300 hover:underline">{{ $globalSettings->corporate_email }}</a></span>@endif</div></div></div>
            </div>

            <!-- Tombol "CONTACT FORM" -->
            <div class="mt-10 pt-8 border-t border-white/15">
              <button type="button" onclick="openContactModal()" class="w-full inline-flex items-center justify-center font-heading font-bold text-xs tracking-widest text-brand-navy bg-white hover:bg-slate-100 py-4 px-8 rounded shadow-md transition duration-200 uppercase">
                CONTACT FORM
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="send" aria-hidden="true" class="lucide lucide-send w-4 h-4 ml-2.5"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"></path><path d="m21.854 2.147-10.94 10.939"></path></svg>
              </button>
            </div>
          </div>

          <!-- Di Samping Blok Biru: Area Bersih untuk Teks "Our Mission, Values and Motto" -->
          <div class="lg:col-span-6 bg-white rounded-lg p-6 sm:p-10 lg:p-12 border border-brand-grayBorder shadow-sm flex flex-col justify-center"><span class="text-xs font-heading font-bold text-brand-accent tracking-widest uppercase">MEDIA SOSIAL &amp; KOMUNITAS</span><h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight mt-1 mb-8">Terhubung di Saluran Resmi</h3><div class="space-y-4">
@forelse(collect($globalSettings?->social_media ?? [])->sortBy('sort_order') as $social)
@php
  $socialUrl = $social['profile_url'] ?? '';
  $socialUrl = \Illuminate\Support\Str::startsWith($socialUrl, ['http://', 'https://']) ? $socialUrl : '#';
  $platformIcon = $social['platform_icon'] ?? '';
@endphp
<div class="p-4 rounded-lg bg-brand-grayBg border border-slate-200 flex items-start justify-between gap-4 transition hover:border-brand-accent group"><div class="flex items-start gap-3.5"><div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-brand-navy shrink-0 shadow-sm group-hover:text-brand-accent group-hover:border-brand-accent transition">
@if($platformIcon === 'linkedin')
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path></svg>
@elseif($platformIcon === 'instagram')
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path></svg>
@elseif($platformIcon === 'youtube')
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"></path></svg>
@else
<span class="material-symbols-outlined">link</span>
@endif
</div><div><div class="flex items-center gap-2"><h4 class="font-heading font-bold text-sm text-brand-navy group-hover:text-brand-accent transition">{{ $social['platform_name'] ?? '' }}</h4><span class="text-xs text-slate-400 font-mono">{{ $social['handle'] ?? '' }}</span></div><p class="text-xs text-brand-grayMuted leading-relaxed mt-1">{{ $social['description'] ?? '' }}</p></div></div><a href="{{ $socialUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-heading font-semibold text-brand-navy bg-white border border-slate-200 rounded hover:bg-brand-navy hover:text-white transition shrink-0 group-hover:border-brand-navy">{{ $social['action_text'] ?? 'Lihat' }} <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a></div>
@empty
<p class="text-sm text-brand-grayMuted">Kanal media sosial akan segera tersedia.</p>
@endforelse
</div></div>

        </div>

        <!-- Deretan Logo-Logo Klien Tepat di Bawah Area Ini Untuk Menutup Halaman -->
        <div class="border-t border-slate-200 pt-16 bg-brand-grayBg p-8 rounded-xl border"><div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4"><div><div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-white border border-brand-grayBorder text-brand-navy text-xs font-semibold tracking-wider uppercase mb-3"><span class="w-2 h-2 rounded-full bg-brand-accent animate-pulse"></span>LOKASI KANTOR PUSAT &amp; FASILITAS PABRIK</div><h3 class="font-heading font-extrabold text-2xl sm:text-3xl text-brand-navy tracking-tight">Headquarters &amp; Plant Location</h3><p class="text-brand-grayMuted text-sm mt-1">Akses strategis koridor industri Cikarang dengan integrasi rantai pasok prima.</p></div><a href="{{ $mapsButtonUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded bg-brand-navy text-white hover:bg-slate-900 font-heading font-bold text-xs tracking-wider uppercase shadow-sm transition transform hover:-translate-y-0.5"><svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>Buka di Google Maps</a></div><div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch"><div class="lg:col-span-8 rounded-lg overflow-hidden border border-brand-grayBorder shadow-heavy bg-slate-900 relative h-[420px]"><iframe title="Apex Industrial Plant Location Map" class="w-full h-full border-0 grayscale hover:grayscale-0 transition duration-500" src="{{ $mapsEmbedUrl }}" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><div class="absolute bottom-4 left-4 bg-brand-navy/95 backdrop-blur-md border border-white/15 text-white p-3.5 rounded-lg shadow-heavy flex items-center gap-3 pointer-events-none max-w-sm"><div class="w-8 h-8 rounded bg-brand-accent/20 border border-brand-accent/40 flex items-center justify-center text-blue-400 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div><div><span class="text-[11px] font-heading font-bold text-blue-300 tracking-wider uppercase block">{{ $globalSettings?->overlay_title }}</span><span class="text-xs text-slate-200 font-medium block">{{ $globalSettings?->overlay_coordinate }}</span></div></div></div><div class="lg:col-span-4 flex flex-col gap-4 justify-start"><div class="bg-white rounded-lg border border-brand-grayBorder p-6 shadow-sm"><div class="flex items-center gap-2 text-brand-navy font-heading font-bold text-sm uppercase tracking-wide mb-3"><svg class="w-4 h-4 text-brand-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>Alamat Resmi Operasional</div><p class="text-xs text-slate-700 font-medium leading-relaxed mb-4">{{ $globalSettings?->factory_address }}</p><div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs"><span class="text-slate-500">Zona Fasilitas</span><span class="font-mono font-bold text-brand-navy">{{ $globalSettings?->facility_zone }}</span></div></div></div></div></div>

      </div>
    </section>

  </main>

    @php
    $contactModalOpen = $errors->any() || session('success');
  @endphp
  <div aria-labelledby="contact-modal-title" aria-modal="true" class="fixed inset-0 z-[60] items-center justify-center p-4 bg-black/70 backdrop-blur-sm {{ $contactModalOpen ? 'flex' : 'hidden' }}" id="contact-modal" role="dialog">
    <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-xl shadow-2xl border border-brand-grayBorder p-6 sm:p-8">
      <button aria-label="Tutup" class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-brand-navy transition" onclick="closeContactModal()" type="button">
        <span class="material-symbols-outlined">close</span>
      </button>
      <span class="text-xs font-heading font-bold text-brand-accent tracking-widest uppercase">Direct Inquiries</span>
      <h3 class="font-heading font-extrabold text-2xl text-brand-navy tracking-tight mt-1 mb-2" id="contact-modal-title">Kirim Pesan</h3>
      <p class="text-sm text-brand-grayMuted mb-5">Tim kami akan membalas permintaan konsultasi atau penawaran Anda secepatnya.</p>

      @if(session('success'))
        <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm" role="status">{{ session('success') }}</div>
      @endif

      <form action="{{ route('contact.store') }}" class="space-y-4" method="POST">
        @csrf
        <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
          <label for="contact-website">Website</label>
          <input autocomplete="off" id="contact-website" name="website" tabindex="-1" type="text">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1" for="contact-name">Nama <span class="text-red-500">*</span></label>
          <input class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent" id="contact-name" maxlength="255" name="name" required type="text" value="{{ old('name') }}">
          @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="contact-email">Email <span class="text-red-500">*</span></label>
            <input class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent" id="contact-email" maxlength="255" name="email" required type="email" value="{{ old('email') }}">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1" for="contact-phone">Telepon</label>
            <input class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent" id="contact-phone" maxlength="255" name="phone" type="tel" value="{{ old('phone') }}">
            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1" for="contact-service">Layanan yang diminati</label>
          <select class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-accent" id="contact-service" name="service_id">
            <option value="">Pilih layanan (opsional)</option>
            @foreach($services as $serviceOption)
              <option value="{{ $serviceOption->id }}" @selected((string) old('service_id') === (string) $serviceOption->id)>{{ $serviceOption->short_title ?? $serviceOption->service_title }}</option>
            @endforeach
          </select>
          @error('service_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1" for="contact-message">Pesan <span class="text-red-500">*</span></label>
          <textarea class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-accent" id="contact-message" maxlength="5000" name="message" required rows="4">{{ old('message') }}</textarea>
          @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="w-full inline-flex items-center justify-center font-heading font-bold text-xs tracking-widest text-white bg-brand-navy hover:bg-slate-900 py-3.5 px-8 rounded shadow-md transition uppercase" type="submit">Kirim Pesan</button>
      </form>
    </div>
  </div>
  <script>
    function openContactModal() {
      const modal = document.getElementById('contact-modal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeContactModal() {
      const modal = document.getElementById('contact-modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }

    document.getElementById('contact-modal').addEventListener('click', (e) => {
      if (e.target.id === 'contact-modal') closeContactModal();
    });
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeContactModal();
    });
    if (window.location.hash === '#contact-modal' || document.getElementById('contact-modal').classList.contains('flex')) {
      openContactModal();
    }
    window.addEventListener('hashchange', () => {
      if (window.location.hash === '#contact-modal') openContactModal();
    });
  </script>

  @include('layouts.footer')

  <!-- Inisialisasi ikon -->
  <script>
    // Inisialisasi ikon Lucide
    lucide.createIcons();
  </script>

















<script class="">
  (function() {
    const navLinks = document.querySelectorAll('header nav a.nav-link');
    const activeClasses = ["text-brand-navy", "after:content-['']", "after:absolute", "after:bottom-0", "after:left-0", "after:w-full", "after:h-0.5", "after:bg-brand-navy"];
    const inactiveClasses = ["text-brand-grayMuted", "hover:text-brand-navy"];

    function setActiveNav(targetLink) {
      navLinks.forEach(link => {
        activeClasses.forEach(cls => link.classList.remove(cls));
        inactiveClasses.forEach(cls => link.classList.add(cls));
      });
      inactiveClasses.forEach(cls => targetLink.classList.remove(cls));
      activeClasses.forEach(cls => targetLink.classList.add(cls));
    }

    navLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
          setActiveNav(this);
        }
      });
    });

    const sections = [
      { id: 'landing', link: document.querySelector('header nav a.nav-link[href$="#landing"]') },
      { id: 'services', link: document.querySelector('header nav a.nav-link[href$="#services"]') },
      { id: 'works', link: document.querySelector('header nav a.nav-link[href$="#works"]') },
      { id: 'contact', link: document.querySelector('header nav a.nav-link[href$="#contact"]') }
    ];

    window.addEventListener('scroll', () => {
      const scrollY = window.pageYOffset || document.documentElement.scrollTop;
      let current = null;
      sections.forEach(item => {
        const el = document.getElementById(item.id);
        if (el) {
          const top = el.offsetTop - 120;
          const height = el.offsetHeight;
          if (scrollY >= top && scrollY < top + height) {
            current = item.link;
          }
        }
      });
      if (current) {
        setActiveNav(current);
      }
    });
  })();
</script><script class="">
  (function() {
    const navLinks = document.querySelectorAll('header nav a.nav-link');
    const activeClasses = ["text-brand-navy", "after:content-['']", "after:absolute", "after:bottom-0", "after:left-0", "after:w-full", "after:h-0.5", "after:bg-brand-navy"];
    const inactiveClasses = ["text-brand-grayMuted", "hover:text-brand-navy"];

    function clearAllNav() {
      navLinks.forEach(link => {
        activeClasses.forEach(cls => link.classList.remove(cls));
        inactiveClasses.forEach(cls => link.classList.add(cls));
      });
    }

    function setActiveNav(targetLink) {
      clearAllNav();
      if (targetLink) {
        inactiveClasses.forEach(cls => targetLink.classList.remove(cls));
        activeClasses.forEach(cls => targetLink.classList.add(cls));
      }
    }

    const sections = [
      { id: 'landing', link: document.querySelector('header nav a.nav-link[href$="#landing"]') },
      { id: 'services-overview', link: document.querySelector('header nav a.nav-link[href$="#services-overview"]') },
      { id: 'works', link: document.querySelector('header nav a.nav-link[href$="#works"]') },
      { id: 'contact', link: document.querySelector('header nav a.nav-link[href$="#contact"]') }
    ];

    function updateScrollspy() {
      const header = document.querySelector('header');
      const headerOffset = header ? header.offsetHeight + 10 : 90;
      const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

      let matchedLink = null;

      for (let i = 0; i < sections.length; i++) {
        const item = sections[i];
        const el = document.getElementById(item.id);
        if (el && item.link) {
          const rect = el.getBoundingClientRect();
          // Element must intersect the viewport active range
          if (rect.top <= headerOffset && rect.bottom > headerOffset) {
            matchedLink = item.link;
            break;
          }
        }
      }

      // Special check: at very top of page (scrollPosition < 50), activate Landing Page if present
      if (!matchedLink && scrollPosition < 80) {
        const landingItem = sections.find(s => s.id === 'landing');
        if (landingItem) matchedLink = landingItem.link;
      }

      if (matchedLink) {
        setActiveNav(matchedLink);
      } else {
        clearAllNav();
      }
    }

    window.addEventListener('scroll', updateScrollspy, { passive: true });
    window.addEventListener('resize', updateScrollspy, { passive: true });
    document.addEventListener('DOMContentLoaded', updateScrollspy);
    updateScrollspy();
  })();
</script>









</body></html>