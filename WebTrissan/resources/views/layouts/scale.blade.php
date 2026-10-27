<style>
  /*
   * Skala tampilan. Tata letak dirancang agar terbaca nyaman seperti zoom browser ±75-80%,
   * sehingga di layar 100% teks tidak terlalu besar dan padding terasa lega.
   * Variabel --page-zoom dipakai juga untuk mengompensasi satuan viewport (vh) yang tidak ikut ter-zoom.
   */
  html { --page-zoom: 0.72; zoom: var(--page-zoom); }
  @media (min-width: 768px) { html { --page-zoom: 0.85; zoom: var(--page-zoom); } }
  @media (min-width: 1024px) { html { --page-zoom: 0.75; zoom: var(--page-zoom); } }
  @media (min-width: 1600px) { html { --page-zoom: 0.85; zoom: var(--page-zoom); } }
  @media (min-width: 1024px) { html .max-w-7xl, html .max-w-\[1360px\] { max-width: 90rem; } }
  html .min-h-screen { min-height: calc(100vh / var(--page-zoom)); }
  html .max-h-\[92vh\] { max-height: calc(92vh / var(--page-zoom)); }
  html .max-h-\[60vh\] { max-height: calc(60vh / var(--page-zoom)); }
  @media (max-width: 639px) { html .w-\[78vw\] { width: calc(78vw / var(--page-zoom)); } }
  @media (max-width: 1023px) { html body { padding-bottom: 5rem; } }
  @media (max-width: 767px) { html #about-hero { background: linear-gradient(#0d2137, #091829 50%, #071321) !important; } }
  #services-slider { scrollbar-width: none; -ms-overflow-style: none; }
  #services-slider::-webkit-scrollbar { display: none; }
</style>
