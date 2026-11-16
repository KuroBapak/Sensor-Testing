@props(['src', 'alt' => '', 'sizes' => '100vw', 'defer' => false])

{{--
  An <img> that offers smaller WebP copies when they exist. The original stays as the fallback.
  With "defer" nothing is requested until the page's script reads data-src and data-srcset (used for hero slides 2 and 3).
  The image's own proportions are set as an aspect ratio, so its space is reserved before it loads. A ratio is used
  instead of width/height attributes because those would stretch an image that only has a height class.
--}}
@php
    $srcset = \App\Support\ImageVariants::srcset($src);
    $size = \App\Support\ImageVariants::dimensions($src);
    $srcAttribute = $defer ? 'data-src' : 'src';
    $srcsetAttribute = $defer ? 'data-srcset' : 'srcset';
    $extra = $attributes->isNotEmpty() ? ' '.$attributes : '';
    $ratio = $size && ! $attributes->has('style') ? ' style="aspect-ratio: '.$size['width'].' / '.$size['height'].'"' : '';
@endphp
@if($srcset)
<picture style="display: contents">
  <source type="image/webp" sizes="{{ $sizes }}" {{ $srcsetAttribute }}="{{ $srcset }}">
  <img {{ $srcAttribute }}="{{ $src }}" alt="{{ $alt }}"{!! $extra !!}{!! $ratio !!}>
</picture>
@else
<img {{ $srcAttribute }}="{{ $src }}" alt="{{ $alt }}"{!! $extra !!}{!! $ratio !!}>
@endif
