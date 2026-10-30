@props(['src', 'alt' => '', 'sizes' => '100vw', 'defer' => false])

{{--
  An <img> that offers smaller WebP copies when they exist. The original stays as the fallback.
  With "defer" nothing is requested until the page's script reads data-src and data-srcset (used for hero slides 2 and 3).
--}}
@php
    $srcset = \App\Support\ImageVariants::srcset($src);
    $srcAttribute = $defer ? 'data-src' : 'src';
    $srcsetAttribute = $defer ? 'data-srcset' : 'srcset';
    $extra = $attributes->isNotEmpty() ? ' '.$attributes : '';
@endphp
@if($srcset)
<picture style="display: contents">
  <source type="image/webp" sizes="{{ $sizes }}" {{ $srcsetAttribute }}="{{ $srcset }}">
  <img {{ $srcAttribute }}="{{ $src }}" alt="{{ $alt }}"{!! $extra !!}>
</picture>
@else
<img {{ $srcAttribute }}="{{ $src }}" alt="{{ $alt }}"{!! $extra !!}>
@endif
