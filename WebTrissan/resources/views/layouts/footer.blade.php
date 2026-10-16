<!-- Footer Penutup Sederhana -->
<footer id="master-footer" class="w-full bg-[#040911] text-slate-200 border-t border-white/10 relative">
  <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(14,165,233,0.08),rgba(255,255,255,0))] pointer-events-none"></div>
  <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12 pt-16 pb-12">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-14 items-start">
      <!-- Kolom Kiri: Logo, Deskripsi, Gambar Partner -->
      <div class="md:col-span-5 space-y-6">
        <a href="{{ url('/') }}" class="flex items-center gap-3.5 group">
          <div class="w-11 h-11 rounded-lg overflow-hidden border border-white/20 flex items-center justify-center bg-white shadow-sm transition group-hover:scale-105 shrink-0">
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCiMPbIsbS-sC9J4DTxtsdSwTVeBEABqrpT_kyCq3jV6W5K1bOH8NOSdxswUhegkBvpOBQsbNnQCkpHCSZVOkGYzisKUZn9NESn9dVQlSR1cMINm9F4zGmpZ7-wcyktFR5YG7eAQVSieih4YiiLAs808ouuT9UMz-7VXekJTTRRHErI1fXIfdGZgJorwjnRKJwQvqz3_Otqvt8F9_RTYXbxS4dQWRCPCnUH4b_Nvr0rswPl2qvjwLG6" alt="PT. Trissan Logo" class="w-full h-full object-cover">
          </div>
          <div class="flex flex-col">
            <span class="font-heading font-extrabold text-xl text-white tracking-tight leading-none group-hover:text-blue-400 transition">PT. Trissan</span>
            <span class="text-[10px] font-semibold text-slate-400 tracking-widest uppercase mt-0.5">Industrial Engineering</span>
          </div>
        </a>
        <p class="text-sm text-slate-400 leading-relaxed max-w-md">
          Mitra manufaktur presisi tier-1 berstandar global. Mengkhususkan diri pada permesinan CNC 5-sumbu, pengelasan orbital bertekanan tinggi, dan integrasi mekatronika otomatis untuk sektor kedirgantaraan, energi, dan alat berat.
        </p>
        <div class="w-full max-w-sm rounded-lg border border-slate-700/60 bg-white/5 p-1.5 shadow-sm overflow-hidden">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDUHSxtTMgXJMPz_oY9plipZG-hSnmDiY3VnqLHppZAzpBSpDb1OAa6NWrAtzWm3ONexnhqlbgU6rimppsYKMXUkDZpoTjiFyEZUqkeujt4YCNwWS2nqCRVcnhiUYF_FDtdbC_PITQ_iwYm7GpyFZUn7GU41be-Zld71LZ63yKru8-uRgc0atTT5UeGic7lTXdyU7BlroF4YM-uzSVeoLDZNhFlcGkD1F945Ocu5otju1DquhtG967Gp0q3FWA2ZP7ryQ" alt="Our Partner" class="w-full h-auto object-contain rounded">
        </div>
      </div>

      <!-- Kolom Tengah: Navigasi Utama -->
      <div class="md:col-span-3 space-y-4">
        <p class="text-xs font-mono font-bold tracking-widest text-blue-400 uppercase">NAVIGASI UTAMA</p>
        <ul class="space-y-2.5 text-sm text-slate-300 font-medium">
          <li><a href="{{ url('/') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">01.</span> Landing Page</a></li>
          <li><a href="{{ url('/#services') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">02.</span> Layanan & Kapabilitas</a></li>
          <li><a href="{{ url('/#works') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">03.</span> Featured Industrial Works</a></li>
          <li><a href="{{ url('/works') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">04.</span> Portofolio Manpower</a></li>
          <li><a href="{{ url('/about') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">05.</span> About Us</a></li>
          <li><a href="{{ url('/license') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">06.</span> License & Certification</a></li>
          <li><a href="{{ url('/#contact') }}" class="hover:text-white hover:translate-x-1 inline-flex items-center gap-2 transition-all"><span class="text-blue-500 font-mono text-[10px]">07.</span> Contact Us / Hubungi Kami</a></li>
        </ul>
      </div>

      <!-- Kolom Kanan: Kantor Pusat & Fasilitas Manufaktur -->
      <div class="md:col-span-4 space-y-4">
        <p class="text-xs font-mono font-bold tracking-widest text-blue-400 uppercase">KANTOR PUSAT & FASILITAS MANUFAKTUR</p>
        <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
          <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          </div>
          <div>
            <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">LOKASI PABRIK & KANTOR</span>
            <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5">Kawasan Industri MM2100, Jl. Irian Blok V, Blok C-18 No. 4, Cikarang, Bekasi 17530, Jawa Barat, Indonesia</p>
          </div>
        </div>
        <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
          <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
          </div>
          <div>
            <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">TELEPON HOTLINE & WHATSAPP</span>
            <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5"><span class="text-white font-semibold">+62 (21) 5508-8990</span> / <span class="text-white font-semibold">+62 811-9870-221</span></p>
          </div>
        </div>
        <div class="p-3.5 rounded-lg bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors flex items-start gap-3.5 group">
          <div class="w-8 h-8 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z"></path></svg>
          </div>
          <div>
            <span class="text-[10px] font-mono tracking-wider text-slate-500 uppercase block">SURAT ELEKTRONIK (EMAIL):</span>
            <p class="text-xs text-slate-300 font-medium leading-relaxed mt-0.5"><a href="mailto:contact@apex-engineering.co.id" class="text-blue-400 hover:underline">contact@apex-engineering.co.id</a> · <a href="mailto:corporate@apex-engineering.co.id" class="text-blue-400 hover:underline">corporate@apex-engineering.co.id</a></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Sub-footer / Bottom bar -->
    <div class="border-t border-slate-800/80 mt-12 pt-6 flex flex-col md:flex-row items-center justify-between text-xs text-slate-400 gap-4">
      <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
        <span>© 2025 PT Apex Industrial Engineering. All rights reserved.</span>
      </div>
      <div class="flex flex-wrap gap-6 items-center text-[11px] font-mono">
        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
        <a href="#" class="hover:text-white transition-colors">Quality & Safety Policy</a>
        <a href="#" class="hover:text-white transition-colors">ISO 9001 / AS9100D Compliance</a>
      </div>
    </div>
  </div>
</footer>
