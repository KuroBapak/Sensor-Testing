{{--
  Phone navigation: the hamburger in the header shows or hides the bottom tab bar (remembered while browsing),
  and one track slides to the tab you pick, also when the next page loads. Without JavaScript the tab bar stays visible.
--}}
<script>
  (function () {
    var root = document.documentElement;
    var open = false;
    try { open = sessionStorage.getItem('bottomNavOpen') === '1'; } catch (e) {}
    root.dataset.bottomNav = open ? 'open' : 'closed';
    root.classList.add('bn-js');
  })();
</script>
<style>
  .bn-toggle{position:relative;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;width:calc(40px / var(--page-zoom,1));height:calc(40px / var(--page-zoom,1));border:0;border-radius:8px;background:transparent;color:#0A192F;cursor:pointer;transition:background-color .2s ease}
  .bn-toggle::after{content:'';position:absolute;inset:calc(-2px / var(--page-zoom,1))}
  .bn-toggle svg{width:calc(22px / var(--page-zoom,1));height:calc(22px / var(--page-zoom,1))}
  .bn-toggle:hover{background:#F1F5F9}
  .bn-toggle:focus-visible{outline:2px solid #0066CC;outline-offset:2px}
  .bn-toggle__close,.bn-toggle[aria-expanded="true"] .bn-toggle__open{display:none}
  .bn-toggle[aria-expanded="true"] .bn-toggle__close{display:block}
  @media (min-width:1024px){.bn-toggle{display:none}}
  #bottom-nav{transition:transform .25s ease,visibility 0s linear 0s}
  html[data-bottom-nav="closed"] #bottom-nav{transform:translateY(100%);visibility:hidden;pointer-events:none;transition:transform .25s ease,visibility 0s linear .25s}
  #bottom-nav a{color:#64748B}
  #bottom-nav a:hover,#bottom-nav a[aria-current="page"],#bottom-nav a[data-active="true"]{color:#0A192F}
  #bottom-nav a[data-active="false"]{color:#64748B}
  .bn-track{position:absolute;top:0;left:0;width:var(--w,0);height:2px;border-radius:0 0 2px 2px;background:#0A192F;opacity:0;transform:translateX(var(--x,0));transition:transform .28s cubic-bezier(.4,0,.2,1),width .28s cubic-bezier(.4,0,.2,1),opacity .15s ease;pointer-events:none}
  .bn-track[data-ready]{opacity:1}
  .bn-js .bn-fallback{display:none}
</style>

<header class="sticky top-0 z-50 backdrop-blur-md border-b shadow-sm transition-all duration-300 border-slate-200/80" style="background-color: rgba(255, 255, 255, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 h-14 lg:h-[72px] flex items-center justify-between gap-3">

    <a href="{{ url('/') }}" class="flex items-center gap-2.5 lg:gap-3.5 group min-w-0">
      <div class="w-10 h-10 lg:w-11 lg:h-11 shrink-0 flex items-center justify-center transition group-hover:scale-105">
        @if($globalSettings?->navbar_logo_url)
          <x-responsive-image :src="$globalSettings->navbar_logo_url" :alt="$globalSettings->company_name" sizes="48px" class="w-full h-full object-contain" />
        @endif
      </div>
      <div class="flex flex-col min-w-0">
        <span class="font-heading font-extrabold text-sm sm:text-base lg:text-base xl:text-lg lg:whitespace-nowrap text-brand-navy tracking-tight leading-tight lg:leading-none group-hover:text-brand-accent transition">{{ $globalSettings?->company_name }}</span>
        <span class="hidden sm:block text-[10px] font-semibold text-brand-grayMuted tracking-widest uppercase mt-0.5">{{ $globalSettings?->company_tagline }}</span>
      </div>
    </a>

    <nav class="hidden lg:flex items-center space-x-5 xl:space-x-8 whitespace-nowrap">
      <a href="{{ url('/#landing') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('/') ? 'text-brand-navy after:content-[""] after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-brand-navy' : 'text-brand-grayMuted hover:text-brand-navy' }}">Beranda</a>

      <div class="relative group">
        <button class="flex items-center gap-1.5 font-heading text-sm font-semibold tracking-wide transition relative py-1 {{ Request::is('works') || Request::is('portofolio') ? 'text-brand-navy font-bold' : 'text-brand-grayMuted hover:text-brand-navy' }} focus:outline-none">
          <span>Layanan & Proyek</span>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-navy transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 top-full pt-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50">
          <div class="bg-slate-900/95 backdrop-blur-md border border-slate-700/60 rounded-xl shadow-2xl p-2 min-w-[240px] flex flex-col gap-1 text-left">
            <a href="{{ url('/#services-overview') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Layanan</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/works') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Proyek Unggulan</div>
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
          <span>Perusahaan & Kredensial</span>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-brand-navy transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 top-full pt-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-200 z-50">
          <div class="bg-slate-900/95 backdrop-blur-md border border-slate-700/60 rounded-xl shadow-2xl p-2 min-w-[240px] flex flex-col gap-1 text-left">
            <a href="{{ url('/about') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Tentang Kami</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
            <a href="{{ url('/license') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 transition flex items-center justify-between group/item">
              <div>
                <div class="font-heading font-bold text-slate-100 group-hover/item:text-blue-400 transition">Lisensi & Sertifikasi</div>
              </div>
              <svg class="w-3.5 h-3.5 text-slate-400 group-hover/item:translate-x-1 group-hover/item:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
          </div>
        </div>
      </div>

      <a href="{{ url('/#contact') }}" class="nav-link font-heading text-sm font-semibold tracking-wide transition relative py-1 text-brand-grayMuted hover:text-brand-navy">Kontak</a>
    </nav>

    {{-- Shows or hides the bottom tab bar on phones. Hex colors, not brand-*: the pages configure different palettes. --}}
    <button type="button" id="bottom-nav-toggle" class="bn-toggle" aria-controls="bottom-nav" aria-expanded="false" aria-label="Buka menu navigasi">
      <svg class="bn-toggle__open" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
      <svg class="bn-toggle__close" aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6L6 18"></path></svg>
    </button>
  </div>
</header>

<nav aria-label="Navigasi utama" class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 shadow-[0_-4px_12px_rgba(15,23,42,0.06)]" id="bottom-nav" style="padding-bottom: env(safe-area-inset-bottom);">
  <div class="max-w-xl mx-auto flex" style="position:relative">
      <span class="bn-track" aria-hidden="true"></span>
      <a aria-current="{{ Request::is('/') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('/') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/') }}">
        @if(Request::is('/'))<span class="bn-fallback absolute top-0 inset-x-4 h-0.5 rounded-b" style="background:#0A192F"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"></path></svg>
        <span>Beranda</span>
      </a>
      <a aria-current="{{ Request::is('works') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('works') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/works') }}">
        @if(Request::is('works'))<span class="bn-fallback absolute top-0 inset-x-4 h-0.5 rounded-b" style="background:#0A192F"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM12 12l8-4.5M12 12v9M12 12L4 7.5"></path></svg>
        <span>Proyek</span>
      </a>
      <a aria-current="{{ Request::is('portofolio') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('portofolio') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/portofolio') }}">
        @if(Request::is('portofolio'))<span class="bn-fallback absolute top-0 inset-x-4 h-0.5 rounded-b" style="background:#0A192F"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
        <span>Portofolio</span>
      </a>
      <a aria-current="{{ Request::is('about') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('about') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/about') }}">
        @if(Request::is('about'))<span class="bn-fallback absolute top-0 inset-x-4 h-0.5 rounded-b" style="background:#0A192F"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 11v5M12 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>Tentang</span>
      </a>
      <a aria-current="{{ Request::is('license') ? 'page' : 'false' }}" class="relative flex-1 flex flex-col items-center justify-center gap-0.5 min-h-16 text-[10px] font-heading font-semibold tracking-wide transition {{ Request::is('license') ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy' }} focus:outline-none focus-visible:bg-slate-100" href="{{ url('/license') }}">
        @if(Request::is('license'))<span class="bn-fallback absolute top-0 inset-x-4 h-0.5 rounded-b" style="background:#0A192F"></span>@endif
        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3zM9 12l2 2 4-4"></path></svg>
        <span>Lisensi</span>
      </a>
  </div>
</nav>

<script>
  (function () {
    var root = document.documentElement;
    var nav = document.getElementById('bottom-nav');
    var toggle = document.getElementById('bottom-nav-toggle');
    var track = nav && nav.querySelector('.bn-track');
    if (!nav || !track) return;

    var store = {
      get: function (key) { try { return sessionStorage.getItem(key); } catch (e) { return null; } },
      set: function (key, value) { try { sessionStorage.setItem(key, value); } catch (e) {} }
    };
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var links = Array.prototype.slice.call(nav.querySelectorAll('a[href]'));
    var current = links.filter(function (link) { return link.getAttribute('aria-current') === 'page'; })[0] || null;

    function setOpen(open) {
      root.dataset.bottomNav = open ? 'open' : 'closed';
      store.set('bottomNavOpen', open ? '1' : '0');
      if (toggle) {
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
      }
    }

    function place(link, animate) {
      if (!link) { track.removeAttribute('data-ready'); return; }
      track.style.transition = animate ? '' : 'none';
      track.style.setProperty('--x', (link.offsetLeft + 16) + 'px');
      track.style.setProperty('--w', Math.max(link.offsetWidth - 32, 0) + 'px');
      track.setAttribute('data-ready', '');
      if (!animate) { void track.offsetWidth; track.style.transition = ''; }
    }

    // The floating contact button sits just above the tab bar, whatever its height on this page.
    function measure() { root.style.setProperty('--bn-h', nav.offsetHeight + 'px'); }
    measure();

    function mark(active) {
      links.forEach(function (link) { link.setAttribute('data-active', String(link === active)); });
    }

    if (toggle) {
      setOpen(root.dataset.bottomNav === 'open');
      toggle.addEventListener('click', function () { setOpen(root.dataset.bottomNav !== 'open'); });
    }

    // Arriving from another tab: start on that tab and slide to this page's tab.
    var fromIndex = parseInt(store.get('bottomNavFrom'), 10);
    var from = links[fromIndex] || null;
    var currentIndex = links.indexOf(current);
    store.set('bottomNavFrom', String(currentIndex));
    if (current && from && from !== current && !reduceMotion) {
      place(from, false);
      requestAnimationFrame(function () { requestAnimationFrame(function () { place(current, true); }); });
    } else {
      place(current, false);
    }

    // Picking a tab: slide the track right away, then open the page.
    var leaving = false;
    links.forEach(function (link) {
      link.addEventListener('click', function (event) {
        if (leaving || link === current || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
        event.preventDefault();
        leaving = true;
        mark(link);
        place(link, true);
        store.set('bottomNavFrom', String(links.indexOf(link)));
        setTimeout(function () { window.location.href = link.href; }, reduceMotion ? 0 : 240);
      });
    });

    window.addEventListener('pageshow', function (event) {
      if (!event.persisted) return;
      leaving = false;
      mark(current);
      place(current, false);
    });
    window.addEventListener('resize', function () { measure(); place(current, false); });
  })();
</script>
