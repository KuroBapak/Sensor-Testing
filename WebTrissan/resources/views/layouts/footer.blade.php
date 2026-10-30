<!-- Footer Penutup Sederhana -->
  <footer id="master-footer" class="w-full bg-[#040911] text-slate-200 border-t border-white/10 relative"><div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(14,165,233,0.08),rgba(255,255,255,0))] pointer-events-none"></div>
<div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12 pt-10 lg:pt-14 pb-8 lg:pb-10">
  <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-14 items-start">
    <!-- Kolom Kiri: Logo, Deskripsi, Gambar Partner -->
    <div class="md:col-span-5 space-y-6">
      <a href="{{ url('/#landing') }}" class="flex items-center gap-3.5 group">
        <div class="w-11 h-11 flex items-center justify-center transition group-hover:scale-105 shrink-0">
          @php
            $footerLogo = $globalSettings?->footer_logo_url;
            $partnerImg = $globalSettings?->footer_partner_image_url;
          @endphp
          @if($footerLogo)
            <x-responsive-image :src="$footerLogo" :alt="$globalSettings?->company_name" sizes="64px" loading="lazy" decoding="async" class="w-full h-full object-contain" />
          @endif
        </div>
        <div class="flex flex-col">
          <span class="font-heading font-extrabold text-xl text-white tracking-tight leading-none group-hover:text-blue-400 transition">{{ $globalSettings?->company_name }}</span>
          <span class="text-[10px] font-semibold text-slate-400 tracking-widest uppercase mt-0.5">{{ $globalSettings?->company_tagline }}</span>
        </div>
      </a>
      <p class="text-sm text-slate-400 leading-relaxed max-w-md">
        {{ $globalSettings?->footer_description }}
      </p>
      @if($partnerImg)
        <div class="w-full max-w-sm rounded-lg border border-slate-700/60 bg-white/5 p-1.5 shadow-sm overflow-hidden">
          <x-responsive-image :src="$partnerImg" alt="Logo mitra perusahaan" sizes="(min-width: 1024px) 400px, 90vw" loading="lazy" decoding="async" class="w-full h-auto object-contain rounded" />
        </div>
      @endif
    </div>

    <!-- Kolom Tengah: Navigasi Utama -->
    <div class="md:col-span-3 space-y-4">
      <p class="text-xs font-mono font-bold tracking-widest text-blue-400 uppercase">NAVIGASI UTAMA</p>
      <ul class="space-y-2.5 text-sm text-slate-300 font-medium"><li class=""><a href="{{ url('/#landing') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-400 font-mono text-[10px]">01.</span> Landing Page</a></li>
<li class=""><a href="{{ url('/#services') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">02.</span> Layanan &amp; Kapabilitas</a></li>
<li class=""><a href="{{ url('/#works') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">03.</span> Featured Industrial Works</a></li>
<li class=""><a href="{{ url('/portofolio') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">04.</span> Portofolio</a></li>
<li class=""><a href="{{ url('/about') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">05.</span> About Us</a></li>
<li class=""><a href="{{ url('/license') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">06.</span> License &amp; Certification</a></li>
<li class=""><a href="{{ url('/#contact') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">07.</span> Contact Us / Hubungi Kami</a></li></ul>
    </div>

    <!-- Kolom Kanan: Kantor Pusat & Fasilitas Manufaktur -->
    <div class="md:col-span-4 space-y-4">
      <p class="text-xs font-mono font-bold tracking-widest text-blue-400 uppercase">KANTOR PUSAT &amp; FASILITAS MANUFAKTUR</p>
      <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
        <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div>
          <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">ALAMAT PABRIK &amp; FASILITAS:</span>
          <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5">{{ $globalSettings?->factory_address }}</p>
        </div>
      </div>
      <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
        <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
        </div>
        <div>
          <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">TELEPON HOTLINE &amp; WHATSAPP</span>
          <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5">
            @foreach(($globalSettings?->phones ?? []) as $phone)
              <a href="{{ $phone['tel_url'] ?? '#' }}" class="text-white font-semibold hover:text-blue-300 transition">{{ $phone['display_text'] ?? '' }}</a>{{ !$loop->last ? ' / ' : '' }}
            @endforeach
          </p>
        </div>
      </div>
      <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
        <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"></path></svg>
        </div>
        <div>
          <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">SURAT ELEKTRONIK (EMAIL):</span>
          <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5">
            <a href="mailto:{{ $globalSettings?->primary_email }}" class="text-blue-400 hover:underline">{{ $globalSettings?->primary_email }}</a>
            @if(!empty($globalSettings?->corporate_email))
              · <a href="mailto:{{ $globalSettings->corporate_email }}" class="text-blue-400 hover:underline">{{ $globalSettings->corporate_email }}</a>
            @endif
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Sub-footer / Bottom bar: 1 baris rapi tanpa celah/gap di bawahnya -->
  <div class="border-t border-slate-800/80 mt-8 lg:mt-10 pt-5 flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 gap-4">
    <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
      <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
      <span class="">{{ $globalSettings?->copyright_text }}</span>
    </div>
    <div class="flex flex-wrap gap-6 items-center text-[11px] font-mono">
      @foreach(($globalSettings?->footer_legal_links ?? []) as $legalLink)
        @if(!empty($legalLink['url']) && $legalLink['url'] !== '#')
          <a href="{{ $legalLink['url'] }}" class="hover:text-white transition-colors">{{ $legalLink['label'] ?? '' }}</a>
        @else
          <span>{{ $legalLink['label'] ?? '' }}</span>
        @endif
      @endforeach
    </div>
  </div>
</div></footer>
