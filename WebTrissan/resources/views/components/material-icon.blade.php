@props(['name'])

@php $name = \App\Support\MaterialIcons::has((string) $name) ? $name : 'category'; @endphp

{{--
  A Material Symbols icon. A name that is not in the curated list (see MaterialIcons) is not in the font either, so it shows the generic category icon. The glyph name is kept in a data attribute and drawn by CSS (see layouts/fonts),
  so the name is never part of the page text that search engines and screen readers read.
--}}
<span aria-hidden="true" {{ $attributes->class(['material-symbols-outlined']) }} data-icon="{{ $name }}"></span>
