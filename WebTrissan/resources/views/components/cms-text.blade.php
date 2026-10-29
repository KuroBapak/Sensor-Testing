@props(['value' => null, 'tag' => 'span'])
{{--
    Renders CMS-managed text. When the value is blank the element is still rendered with a
    non-breaking space, so the surrounding spacing stays identical to a filled-in page.
--}}
<{{ $tag }} {{ $attributes }}>{{ filled($value) ? $value : "\u{00A0}" }}</{{ $tag }}>
