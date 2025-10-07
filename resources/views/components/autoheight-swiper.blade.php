@props(['id' => 'autoheight-swiper-' . uniqid(), 'slides' => []])

<div class="autoheight-swiper-container mb-8">
    <div class="swiper {{ $id }}" id="{{ $id }}">
        <div class="swiper-wrapper">
            @foreach($slides as $index => $slide)
                <div class="swiper-slide">
                    <div class="slide-content bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow p-6">
                        @if(is_array($slide))
                            @if(isset($slide['image']))
                                <div class="mb-4">
                                    <img src="{{ $slide['image'] }}" 
                                         alt="{{ $slide['title'] ?? 'Slide ' . ($index + 1) }}" 
                                         class="w-full h-auto rounded-lg object-cover">
                                </div>
                            @endif
                            
                            @if(isset($slide['title']))
                                <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $slide['title'] }}</h3>
                            @endif
                            
                            @if(isset($slide['content']))
                                <div class="text-gray-600 leading-relaxed">
                                    {!! $slide['content'] !!}
                                </div>
                            @endif
                        @else
                            <div class="text-gray-600 leading-relaxed">
                                {!! $slide !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        @if(count($slides) > 1)
            <!-- Navigation arrows -->
            <div class="swiper-button-next !text-blue-600 !bg-white/90 !backdrop-blur-sm !w-12 !h-12 !rounded-full !shadow-lg !border !border-gray-200 after:!text-xl after:!font-semibold !transition-all !duration-300 hover:!bg-white hover:!shadow-xl hover:!scale-110"></div>
            <div class="swiper-button-prev !text-blue-600 !bg-white/90 !backdrop-blur-sm !w-12 !h-12 !rounded-full !shadow-lg !border !border-gray-200 after:!text-xl after:!font-semibold !transition-all !duration-300 hover:!bg-white hover:!shadow-xl hover:!scale-110"></div>
            
            <!-- Pagination dots -->
            <div class="swiper-pagination !bottom-6 [&_.swiper-pagination-bullet]:!bg-white/70 [&_.swiper-pagination-bullet]:!w-3 [&_.swiper-pagination-bullet]:!h-3 [&_.swiper-pagination-bullet-active]:!bg-white [&_.swiper-pagination-bullet]:!transition-all [&_.swiper-pagination-bullet]:!duration-300 [&_.swiper-pagination-bullet-active]:!scale-125"></div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AutoHeight Swiper
    if (typeof Swiper !== 'undefined') {
        const swiperElement = document.querySelector('#{{ $id }}');
        if (swiperElement && !swiperElement.classList.contains('swiper-initialized')) {
            new Swiper('#{{ $id }}', {
                autoHeight: true,
                spaceBetween: 20,
                navigation: {
                    nextEl: '#{{ $id }} .swiper-button-next',
                    prevEl: '#{{ $id }} .swiper-button-prev',
                },
                pagination: {
                    el: '#{{ $id }} .swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 20
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 20
                    }
                }
            });
        }
    }
});
</script>
