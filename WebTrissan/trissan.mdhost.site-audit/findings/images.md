# Images — 40/100

**What works**
- Most images have descriptive Indonesian alt text

## [High] Very large image files

Sampled 18 images = 7.1 MB. work-7.jpg 785 KB, work-5.jpg 623 KB, logo.png 336 KB, about-hero/teaser PNG 388 KB each.

**Fix:** Resize to display size (max ~1600px), convert to WebP/AVIF, logo to SVG or a <20 KB PNG.

## [Medium] No width/height attributes

0 of 47 img tags have width/height, causing layout shift (CLS).

**Fix:** Add width and height (or aspect-ratio CSS) to every img.

## [Medium] No lazy loading on homepage

All 13 homepage images load eagerly, including footer images.

**Fix:** loading="lazy" on below-the-fold images; fetchpriority="high" on the first hero image.

## [Low] Missing alt text

One image without alt on each of /portofolio, /license and /works.

**Fix:** Add alt text (or alt="" if decorative).

