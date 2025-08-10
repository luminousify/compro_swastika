@props([
    'src',
    'alt',
    'aspectRatio' => '16-9',
    'sizes' => '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 33vw',
    'loading' => 'lazy',
    'class' => '',
    'fallback' => null,
    'webp' => true
])

@php
    $containerClass = "image-container aspect-{$aspectRatio} {$class}";
    $imgClass = 'responsive-image lazy-image';
    
    // Generate srcset for different sizes
    $srcset = collect([768, 1280, 1920])->map(function($size) use ($src) {
        $path = preg_replace('/(\.[^.]+)$/', "_${size}w$1", $src);
        return "{$path} {$size}w";
    })->implode(', ');
    
    // Generate WebP srcset if supported
    $webpSrcset = null;
    if ($webp) {
        $webpSrcset = collect([768, 1280, 1920])->map(function($size) use ($src) {
            $path = preg_replace('/(\.[^.]+)$/', "_${size}w.webp", $src);
            return "{$path} {$size}w";
        })->implode(', ');
    }
    
    // Placeholder for lazy loading
    $placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNGNUY1RjUiLz48L3N2Zz4=';
@endphp

<div class="{{ $containerClass }}">
    @if($webp && $webpSrcset)
        <picture>
            <source 
                type="image/webp" 
                @if($loading === 'lazy')
                    data-srcset="{{ $webpSrcset }}"
                @else
                    srcset="{{ $webpSrcset }}"
                @endif
                sizes="{{ $sizes }}"
            >
            <img 
                class="{{ $imgClass }}"
                @if($loading === 'lazy')
                    src="{{ $placeholder }}"
                    data-src="{{ $src }}"
                    data-srcset="{{ $srcset }}"
                @else
                    src="{{ $src }}"
                    srcset="{{ $srcset }}"
                @endif
                sizes="{{ $sizes }}"
                alt="{{ $alt }}"
                loading="{{ $loading }}"
                @if($fallback)
                    data-fallback="{{ $fallback }}"
                @endif
            >
        </picture>
    @else
        <img 
            class="{{ $imgClass }}"
            @if($loading === 'lazy')
                src="{{ $placeholder }}"
                data-src="{{ $src }}"
                data-srcset="{{ $srcset }}"
            @else
                src="{{ $src }}"
                srcset="{{ $srcset }}"
            @endif
            sizes="{{ $sizes }}"
            alt="{{ $alt }}"
            loading="{{ $loading }}"
            @if($fallback)
                data-fallback="{{ $fallback }}"
            @endif
        >
    @endif
</div>