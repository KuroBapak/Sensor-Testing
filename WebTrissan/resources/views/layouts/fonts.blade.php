{{--
  Fonts for every public page. Montserrat, Inter and JetBrains Mono are self-hosted by the Vite plugin
  (see vite.config.js), so no third-party stylesheet blocks rendering. Only the icon font still comes from
  Google, as a file that holds just the curated icons (about 14 KB instead of the 1.1 MB variable font).
  Pass "mono" => true on pages that use the monospace font.
--}}
@fonts(($mono ?? false) ? ['montserrat', 'inter', 'jetbrains-mono'] : ['montserrat', 'inter'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{{ \App\Support\MaterialIcons::fontUrl() }}">
<style>.material-symbols-outlined::before{content:attr(data-icon)}</style>
