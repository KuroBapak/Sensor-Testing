<!-- 1. Bilah Navigasi (Header) -->
<header class="sticky top-0 z-50 backdrop-blur-md border-b shadow-sm transition-all duration-300 border-slate-200/80" style="background-color: rgba(255, 255, 255, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 h-14 lg:h-[72px] flex items-center justify-between gap-3">
    <!-- Logo Perusahaan di Sebelah Kiri -->
    <a href="{{ url('/') }}" class="flex items-center gap-2.5 lg:gap-3.5 group min-w-0">
      <div class="w-10 h-10 lg:w-11 lg:h-11 shrink-0 flex items-center justify-center transition group-hover:scale-105">
        @if($globalSettings?->navbar_logo_url)
          <img src="{{ $globalSettings->navbar_logo_url }}" alt="{{ $globalSettings->company_name }}" class="w-full h-full object-contain">
        @endif
      </div>
      <div class="flex flex-col min-w-0">
        <span class="font-heading font-extrabold text-sm sm:text-base lg:text-base xl:text-lg lg:whitespace-nowrap text-brand-navy tracking-tight leading-tight lg:leading-none group-hover:text-brand-accent transition">{{ $globalSettings?->company_name }}</span>
        <span class="hidden sm:block text-[10px] font-semibold text-brand-grayMuted tracking-widest uppercase mt-0.5">{{ $globalSettings?->company_tagline }}</span>
      </div>
    </a>

    <!-- Tautan Menu di Sebelah Kanan -->
    <nav class="hidden lg:flex items-center space-x-5 xl:space-x-8 whitespace-nowrap">
      <a href="{{ url('/#landing') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('/') ? 'text-brand-navy after:content-[""] after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-brand-navy' : 'text-brand-grayMuted hover:text-brand-navy' }}">Landing Page</a>

      <div class="relative group">
        <button class="flex items-center gap-1.5 font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('works') || Request::is('portofolio') ? 'text-brand-navy font-bold' : 'text-brand-grayMuted hover:text-brand-navy' }} focus:outline-none">
          <span>Capabilities & Works</span>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-navy transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 top-full pt-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50">
          <div class="bg-slate-900/95 backdrop-blur-md border border-slate-700/60 rounded-xl shadow-2xl p-2 min-w-[240px] flex flex-col gap-1 text-left">
            <a href="{{ url('/#services-overview') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Services</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/works') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Featured Works</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/portofolio') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Portofolio</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
          </div>
        </div>
      </div>

      <div class="relative group">
        <button class="flex items-center gap-1.5 font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('about') || Request::is('license') ? 'text-brand-navy font-bold' : 'text-brand-grayMuted hover:text-brand-navy' }} focus:outline-none">
          <span>Company & Credentials</span>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-navy transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 top-full pt-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50">
          <div class="bg-slate-900/95 backdrop-blur-md border border-slate-700/60 rounded-xl shadow-2xl p-2 min-w-[240px] flex flex-col gap-1 text-left">
            <a href="{{ url('/about') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">About Us</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/license') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">License & Certification</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
          </div>
        </div>
      </div>

      <a href="{{ url('/#contact') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 text-brand-grayMuted hover:text-brand-navy">Contact</a>
    </nav>
    <!-- Tombol kontak (mobile) -->
    <a class="lg:hidden shrink-0 inline-flex items-center gap-1.5 px-4 min-h-11 rounded-lg bg-brand-navy text-white text-xs font-heading font-bold tracking-wide hover:bg-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent transition" href="{{ url('/#contact') }}">
      <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h2.5l1.5 4-2 1.5a11 11 0 005 5L13.5 11l4 1.5V15a2 2 0 01-2 2C8.7 17 3 11.3 3 5z"></path></svg>
      Kontak
    </a>
  </div>
</header>

<!-- Navigasi bawah (mobile) -->
<nav aria-label="Navigasi utama" class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_12px_rgba(15,23,42,0.06)]" id="bottom-nav" style="padding-bottom: env(safe-area-inset-bottom);">
  <div class="max-w-xl mx-auto flex">
      <a aria-current="{{ Request::is('/') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('/') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/') }}">
        @if(Request::is('/'))<span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand-navy"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"></path></svg>
        <span>Home</span>
      </a>
      <a aria-current="{{ Request::is('works') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('works') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/works') }}">
        @if(Request::is('works'))<span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand-navy"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM12 12l8-4.5M12 12v9M12 12L4 7.5"></path></svg>
        <span>Works</span>
      </a>
      <a aria-current="{{ Request::is('portofolio') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('portofolio') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/portofolio') }}">
        @if(Request::is('portofolio'))<span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand-navy"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
        <span>Portofolio</span>
      </a>
      <a aria-current="{{ Request::is('about') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('about') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/about') }}">
        @if(Request::is('about'))<span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand-navy"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 11v5M12 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>About</span>
      </a>
      <a aria-current="{{ Request::is('license') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('license') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/license') }}">
        @if(Request::is('license'))<span class="absolute top-0 inset-x-4 h-0.5 rounded-b bg-brand-navy"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3zM9 12l2 2 4-4"></path></svg>
        <span>Lisensi</span>
      </a>
  </div>
</nav>
