<!-- 1. Bilah Navigasi (Header) -->
<header class="sticky top-0 z-50 backdrop-blur-md border-b shadow-sm transition-all duration-300 border-slate-200/80" style="background-color: rgba(255, 255, 255, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 h-16 lg:h-20 flex items-center justify-between gap-3">
    <!-- Logo Perusahaan di Sebelah Kiri -->
    <a href="{{ url('/') }}" class="flex items-center gap-2.5 lg:gap-3.5 group min-w-0">
      <div class="w-10 h-10 lg:w-11 lg:h-11 shrink-0 rounded-lg overflow-hidden border border-brand-navy/15 flex items-center justify-center bg-white shadow-sm transition group-hover:scale-105">
        <img src="{{ $globalSettings?->navbar_logo_url ?? 'https://lh3.googleusercontent.com/aida-public/AB6AXuCiMPbIsbS-sC9J4DTxtsdSwTVeBEABqrpT_kyCq3jV6W5K1bOH8NOSdxswUhegkBvpOBQsbNnQCkpHCSZVOkGYzisKUZn9NESn9dVQlSR1cMINm9F4zGmpZ7-wcyktFR5YG7eAQVSieih4YiiLAs808ouuT9UMz-7VXekJTTRRHErI1fXIfdGZgJorwjnRKJwQvqz3_Otqvt8F9_RTYXbxS4dQWRCPCnUH4b_Nvr0rswPl2qvjwLG6' }}" alt="{{ $globalSettings?->company_name ?? 'Apex Industrial Logo' }}" class="w-full h-full object-cover">
      </div>
      <div class="flex flex-col min-w-0">
        <span class="font-heading font-extrabold text-sm sm:text-base lg:text-lg xl:text-xl lg:whitespace-nowrap text-brand-navy tracking-tight leading-tight lg:leading-none group-hover:text-brand-accent transition">{{ $globalSettings?->company_name ?? 'PT. Apex Industrial Engineering' }}</span>
        <span class="hidden sm:block text-[10px] font-semibold text-brand-grayMuted tracking-widest uppercase mt-0.5">{{ $globalSettings?->company_tagline ?? 'Industrial Engineering' }}</span>
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
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $navServiceCount ?? 12 }} Layanan dalam {{ $navClusterCount ?? 4 }} Klaster</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/works') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Featured Works</div>
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">Output & Dokumentasi Proyek</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/portofolio') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Portofolio</div>
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">Tenaga Kerja & Fabrikasi Lapangan</div>
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
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">Profil Pabrik, Visi & Standar Mutu</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/license') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">License & Certification</div>
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">AS9100D, ISO 9001 & Legalitas Resmi</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
          </div>
        </div>
      </div>

      <a href="{{ url('/#contact') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 text-brand-grayMuted hover:text-brand-navy">Contact</a>
    </nav>
    <!-- Tombol menu mobile -->
    <button aria-controls="mobile-menu" aria-expanded="false" aria-label="Buka menu navigasi" class="lg:hidden shrink-0 w-11 h-11 rounded-lg border border-slate-200 bg-white/80 text-brand-navy flex items-center justify-center hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-accent transition" id="mobile-menu-toggle" type="button">
      <svg aria-hidden="true" class="w-6 h-6" data-icon="open" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
      <svg aria-hidden="true" class="w-6 h-6 hidden" data-icon="close" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"></path></svg>
    </button>
  </div>

  <!-- Panel menu mobile -->
  <div class="hidden lg:hidden border-t border-slate-200 bg-white max-h-[calc(100vh-4rem)] overflow-y-auto" id="mobile-menu">
    <nav aria-label="Menu utama" class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex flex-col text-sm font-heading font-semibold">
      <a class="min-h-11 flex items-center rounded-lg px-3 {{ Request::is('/') ? 'text-brand-navy bg-slate-100' : 'text-brand-grayMuted hover:bg-slate-50' }}" href="{{ url('/#landing') }}">Landing Page</a>
      <div class="px-3 pt-3 pb-1 text-[11px] font-mono uppercase tracking-widest text-slate-400">Capabilities &amp; Works</div>
      <a class="min-h-11 flex items-center rounded-lg px-3 text-brand-grayMuted hover:bg-slate-50" href="{{ url('/#services-overview') }}">Services <span class="ml-2 text-xs font-normal text-slate-400">{{ $navServiceCount ?? 12 }} layanan</span></a>
      <a class="min-h-11 flex items-center rounded-lg px-3 {{ Request::is('works') ? 'text-brand-navy bg-slate-100' : 'text-brand-grayMuted hover:bg-slate-50' }}" href="{{ url('/works') }}">Featured Works</a>
      <a class="min-h-11 flex items-center rounded-lg px-3 {{ Request::is('portofolio') ? 'text-brand-navy bg-slate-100' : 'text-brand-grayMuted hover:bg-slate-50' }}" href="{{ url('/portofolio') }}">Portofolio</a>
      <div class="px-3 pt-3 pb-1 text-[11px] font-mono uppercase tracking-widest text-slate-400">Company &amp; Credentials</div>
      <a class="min-h-11 flex items-center rounded-lg px-3 {{ Request::is('about') ? 'text-brand-navy bg-slate-100' : 'text-brand-grayMuted hover:bg-slate-50' }}" href="{{ url('/about') }}">About Us</a>
      <a class="min-h-11 flex items-center rounded-lg px-3 {{ Request::is('license') ? 'text-brand-navy bg-slate-100' : 'text-brand-grayMuted hover:bg-slate-50' }}" href="{{ url('/license') }}">License &amp; Certification</a>
      <a class="min-h-11 mt-2 flex items-center justify-center rounded-lg px-3 bg-brand-navy text-white" href="{{ url('/#contact') }}">Contact</a>
    </nav>
  </div>
</header>

<script>
  (function () {
    const toggle = document.getElementById('mobile-menu-toggle');
    const menu = document.getElementById('mobile-menu');
    if (!toggle || !menu) return;

    function setOpen(open) {
      menu.classList.toggle('hidden', !open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
      toggle.querySelector('[data-icon="open"]').classList.toggle('hidden', open);
      toggle.querySelector('[data-icon="close"]').classList.toggle('hidden', !open);
    }

    toggle.addEventListener('click', () => setOpen(menu.classList.contains('hidden')));
    menu.addEventListener('click', (e) => {
      if (e.target.closest('a')) setOpen(false);
    });
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });
    window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
      if (e.matches) setOpen(false);
    });
  })();
</script>
