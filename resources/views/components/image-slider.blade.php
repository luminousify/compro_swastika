@props(['media', 'id' => 'slider-' . uniqid()])

@if($media && $media->count() > 0)
<div class="image-slider-container mb-8">
    <div class="swiper-container {{ $media->count() === 1 ? 'single-image' : '' }}" 
         id="{{ $id }}"
         data-swiper-autoplay="5000" 
         data-swiper-pause-on-hover="true"
         data-swiper-keyboard="true"
         data-swiper-touch="true"
         data-swiper-simulate-touch="true">
        
        @php
            $sliderPadding = 120; // height is 120% of width (~5:6 aspect ratio)
        @endphp

        <div class="swiper-wrapper">
            @foreach($media as $item)
                <div class="swiper-slide">
                    <div class="relative w-full max-w-4xl mx-auto overflow-hidden rounded-xl bg-black shadow-lg">
                        <div class="relative w-full" style="padding-bottom: {{ $sliderPadding }}%; min-height: 480px; aspect-ratio: 5 / 6;">
                            <img src="{{ $item->url }}" 
                                 alt="{{ $item->caption ?? 'Image' }}" 
                                 class="absolute inset-0 w-full h-full object-cover"
                                 loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                 decoding="async"
                                 fetchpriority="{{ $loop->first ? 'high' : 'low' }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($media->count() > 1)
            <!-- Navigation arrows -->
            <div class="swiper-button-next !text-blue-600 !bg-white/90 !backdrop-blur-sm !w-12 !h-12 !rounded-full !shadow-lg !border !border-gray-200 after:!text-xl after:!font-semibold !transition-all !duration-300 hover:!bg-white hover:!shadow-xl hover:!scale-110"></div>
            <div class="swiper-button-prev !text-blue-600 !bg-white/90 !backdrop-blur-sm !w-12 !h-12 !rounded-full !shadow-lg !border !border-gray-200 after:!text-xl after:!font-semibold !transition-all !duration-300 hover:!bg-white hover:!shadow-xl hover:!scale-110"></div>
            
            <!-- Pagination dots -->
            <div class="swiper-pagination !bottom-6 [&_.swiper-pagination-bullet]:!bg-white/70 [&_.swiper-pagination-bullet]:!w-3 [&_.swiper-pagination-bullet]:!h-3 [&_.swiper-pagination-bullet-active]:!bg-white [&_.swiper-pagination-bullet]:!transition-all [&_.swiper-pagination-bullet]:!duration-300 [&_.swiper-pagination-bullet-active]:!scale-125"></div>
        @endif
    </div>
</div>

{{-- Swiper initialization is now handled globally in app.js --}}
@else
<div class="no-images-placeholder mb-8">
    <div class="max-w-3xl mx-auto bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center py-16">
        <div class="text-center text-gray-400">
            <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                      d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/>
            </svg>
            <p class="text-sm font-medium">No images available</p>
            <p class="text-xs mt-1">Images will appear here when uploaded</p>
        </div>
    </div>
</div>
@endif