{{--
  Floating "Hubungi Kami" button and the contact form it opens, on every public page.
  The form, its fields and its behaviour (CMS success message, WhatsApp hand-off, honeypot) are the ones the landing
  page's contact section opens too, so there is a single copy. Colors are hex on purpose: the pages configure
  different Tailwind palettes. Open it with openContactModal() or the #contact-modal address.
--}}
<style>
  /* The pages are drawn with a page-wide zoom (--page-zoom), so sizes use --u: one real screen pixel. */
  :root{--u:calc(1px / var(--page-zoom,1))}
  .contact-fab{position:fixed;right:calc(16 * var(--u));bottom:calc(env(safe-area-inset-bottom,0px) / var(--page-zoom,1) + 16 * var(--u));z-index:45;display:inline-flex;align-items:center;justify-content:center;gap:calc(10 * var(--u));min-width:calc(56 * var(--u));height:calc(56 * var(--u));padding:0 calc(16 * var(--u));border:0;border-radius:9999px;background:#0A192F;color:#fff;font-family:Montserrat,sans-serif;font-size:calc(13 * var(--u));font-weight:700;letter-spacing:.02em;line-height:1;cursor:pointer;box-shadow:0 8px 24px rgba(6,13,23,.35),inset 0 0 0 1px rgba(255,255,255,.1);transition:transform .2s ease,box-shadow .2s ease,background-color .2s ease,bottom .25s ease}
  .contact-fab svg{width:calc(22 * var(--u));height:calc(22 * var(--u))}
  .contact-fab:hover{background:#0066CC;transform:translateY(-2px);box-shadow:0 12px 28px rgba(6,13,23,.4),inset 0 0 0 1px rgba(255,255,255,.15)}
  .contact-fab:active{transform:translateY(0)}
  .contact-fab:focus-visible{outline:3px solid #0066CC;outline-offset:3px}
  .contact-fab__label{display:none}
  @media (min-width:640px){.contact-fab{right:calc(24 * var(--u));bottom:calc(24 * var(--u));padding:0 calc(20 * var(--u))}.contact-fab__label{display:inline}}
  @media (max-width:1023.98px){html[data-bottom-nav="open"] .contact-fab{bottom:calc(var(--bn-h,64px) + 16 * var(--u))}}
  /*
    The form is written with the landing page's Tailwind classes. Works, portfolio, license and about build their CSS
    from other palettes (no brand-* colors, other spacing and type scales), so the same look is pinned here by id.
    On the landing page these values are identical to what the classes already give.
  */
  #contact-modal{position:fixed;inset:0;z-index:60;align-items:center;justify-content:center;font-family:Inter,sans-serif;font-size:16px;line-height:1.5;color:#1E293B;padding:16px;background:rgb(0 0 0 / .7);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px)}
  #contact-modal>div{position:relative;width:100%;max-width:512px;overflow-y:auto;background:#fff;border:1px solid #E2E8F0;border-radius:12px;padding:24px;box-shadow:0 25px 50px -12px rgb(0 0 0 / .25)}
  @media (min-width:640px){#contact-modal>div{padding:32px}}
  #contact-modal>div>button{position:absolute;top:16px;right:16px;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:9999px;color:#64748B;transition:background-color .15s ease,color .15s ease}
  #contact-modal>div>button:hover{background:#F1F5F9;color:#0A192F}
  #contact-modal>div>span{font-family:Montserrat,sans-serif;font-size:12px;line-height:16px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#0066CC}
  #contact-modal h3{margin:4px 0 8px;font-family:Montserrat,sans-serif;font-size:20px;line-height:1.4;font-weight:800;letter-spacing:-.025em;color:#0A192F}
  #contact-modal>div>p{margin:0 0 20px;font-size:14px;line-height:20px;color:#64748B}
  #contact-modal [role=status]{margin-bottom:20px;padding:12px;border:1px solid #A7F3D0;border-radius:8px;background:#ECFDF5;color:#065F46;font-size:14px;line-height:20px}
  #contact-modal form>:not(:last-child){margin-bottom:16px}
  #contact-modal form .grid{display:grid;grid-template-columns:minmax(0,1fr);gap:16px}
  @media (min-width:640px){#contact-modal form .grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
  #contact-modal label{display:block;margin-bottom:4px;font-size:12px;line-height:16px;font-weight:600;color:#475569}
  #contact-modal label span{color:#EF4444}
  #contact-modal input:not([type="hidden"]),#contact-modal select,#contact-modal textarea{width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;font-size:14px;line-height:20px;outline:none}
  #contact-modal input:focus,#contact-modal select:focus,#contact-modal textarea:focus{box-shadow:0 0 0 2px #0066CC}
  #contact-modal form p{margin-top:4px;font-size:12px;line-height:16px;color:#DC2626}
  #contact-modal form button[type="submit"]{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:14px 32px;border-radius:4px;background:#0A192F;color:#fff;font-family:Montserrat,sans-serif;font-size:12px;line-height:16px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;box-shadow:0 4px 6px -1px rgb(0 0 0 / .1),0 2px 4px -2px rgb(0 0 0 / .1);transition:background-color .2s ease}
  #contact-modal form button[type="submit"]:hover{background:#0F172A}
</style>
<button type="button" id="contact-fab" class="contact-fab" aria-haspopup="dialog" aria-controls="contact-modal" aria-label="Hubungi kami" onclick="openContactModal()">
  <svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path></svg>
  <span class="contact-fab__label">Hubungi Kami</span>
</button>

@php
    $contactModalOpen = $errors->any() || session()->has('success');
  @endphp
  <div aria-labelledby="contact-modal-title" aria-modal="true" class="fixed inset-0 z-[60] items-center justify-center p-4 bg-black/70 backdrop-blur-sm {{ $contactModalOpen ? 'flex' : 'hidden' }}" id="contact-modal" role="dialog">
    <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white rounded-xl shadow-2xl border border-brand-grayBorder p-6 sm:p-8">
      <button aria-label="Tutup" class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-brand-navy transition" onclick="closeContactModal()" type="button">
        <x-material-icon name="close" />
      </button>
      <span class="text-xs font-heading font-bold text-brand-accent tracking-widest uppercase">Konsultasi langsung</span>
      <h3 class="font-heading font-extrabold text-2xl text-brand-navy tracking-tight mt-1 mb-2" id="contact-modal-title">Kirim Pesan</h3>
      <p class="text-sm text-brand-grayMuted mb-5">Tim kami akan membalas permintaan konsultasi atau penawaran Anda secepatnya.</p>

      @if(filled(session('success')))
        <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm" role="status">{{ session('success') }}</div>
      @endif

      @php
        $opensWhatsapp = filled($globalSettings?->whatsappNumber());
      @endphp
      <form action="{{ route('contact.store') }}" class="space-y-4" method="POST" @if($opensWhatsapp) target="_blank" data-opens-whatsapp @endif>
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
            @foreach($contactServices as $serviceOption)
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
    let contactModalOpener = null;

    function openContactModal() {
      const modal = document.getElementById('contact-modal');
      contactModalOpener = document.activeElement;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
      document.getElementById('contact-name')?.focus();
    }

    function closeContactModal() {
      const modal = document.getElementById('contact-modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
      contactModalOpener?.focus();
      contactModalOpener = null;
    }

    document.getElementById('contact-modal').addEventListener('click', (e) => {
      if (e.target.id === 'contact-modal') closeContactModal();
    });
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeContactModal();
    });
    document.querySelectorAll('#contact-modal form[data-opens-whatsapp]').forEach((form) => {
      form.addEventListener('submit', () => {
        setTimeout(() => {
          form.reset();
          closeContactModal();
        }, 300);
      });
    });
    if (window.location.hash === '#contact-modal' || document.getElementById('contact-modal').classList.contains('flex')) {
      openContactModal();
    }
    window.addEventListener('hashchange', () => {
      if (window.location.hash === '#contact-modal') openContactModal();
    });
  </script>
