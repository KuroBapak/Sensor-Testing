<!-- 1. Bilah Navigasi (Header) -->
<header class="sticky top-0 z-50 backdrop-blur-md border-b shadow-sm transition-all duration-300 border-slate-200/80" style="background-color: rgba(255, 255, 255, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
  <div class="max-w-7xl mx-auto px-6 lg:px-12 h-20 flex items-center justify-between">
    <!-- Logo Perusahaan di Sebelah Kiri -->
    <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
      <div class="w-11 h-11 rounded-lg overflow-hidden border border-brand-navy/15 flex items-center justify-center bg-white shadow-sm transition group-hover:scale-105">
        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCiMPbIsbS-sC9J4DTxtsdSwTVeBEABqrpT_kyCq3jV6W5K1bOH8NOSdxswUhegkBvpOBQsbNnQCkpHCSZVOkGYzisKUZn9NESn9dVQlSR1cMINm9F4zGmpZ7-wcyktFR5YG7eAQVSieih4YiiLAs808ouuT9UMz-7VXekJTTRRHErI1fXIfdGZgJorwjnRKJwQvqz3_Otqvt8F9_RTYXbxS4dQWRCPCnUH4b_Nvr0rswPl2qvjwLG6" alt="Apex Industrial Logo" class="w-full h-full object-cover">
      </div>
      <div class="flex flex-col">
        <span class="font-heading font-extrabold text-xl text-brand-navy tracking-tight leading-none group-hover:text-brand-accent transition">PT. Trissan</span>
        <span class="text-[10px] font-semibold text-brand-grayMuted tracking-widest uppercase mt-0.5">Industrial Engineering</span>
      </div>
    </a>

    <!-- Tautan Menu di Sebelah Kanan -->
    <nav class="hidden md:flex items-center space-x-8">
      <a href="{{ url('/') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('/') ? 'text-brand-navy after:content-[""] after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-brand-navy' : 'text-brand-grayMuted hover:text-brand-navy' }}">Landing Page</a>

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
                <div class="text-[11px] text-slate-400 font-normal mt-0.5">12 Klaster & Kapabilitas Rekayasa</div>
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
  </div>
</header>
