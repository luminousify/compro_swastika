import './bootstrap';
import './lazy-loading';
import { Swiper } from 'swiper';
import { Navigation, Pagination, Autoplay, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// Initialize Swiper when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const swiperContainer = document.querySelector('.swiper-container');
    
    if (swiperContainer) {
        const swiper = new Swiper(swiperContainer, {
            modules: [Navigation, Pagination, Autoplay, Keyboard],
            
            // Slider settings
            loop: true,
            centeredSlides: true,
            slidesPerView: 1,
            spaceBetween: 0,
            
            // Autoplay configuration
            autoplay: {
                delay: parseInt(swiperContainer.dataset.swiperAutoplay) || 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: swiperContainer.dataset.swiperPauseOnHover === 'true',
            },
            
            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            
            // Pagination
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            
            // Keyboard navigation
            keyboard: {
                enabled: swiperContainer.dataset.swiperKeyboard === 'true',
                onlyInViewport: true,
            },
            
            // Touch/swipe settings
            touchRatio: swiperContainer.dataset.swiperTouch === 'true' ? 1 : 0,
            simulateTouch: swiperContainer.dataset.swiperSimulateTouch === 'true',
            
            // Responsive breakpoints
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
                768: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                },
                1024: {
                    slidesPerView: 1,
                    spaceBetween: 0,
                }
            },
            
            // Events
            on: {
                init: function() {
                    // Remove loading state when slider is initialized
                    const loadingElement = swiperContainer.querySelector('.swiper-loading');
                    if (loadingElement) {
                        loadingElement.remove();
                    }
                },
                slideChange: function() {
                    // Add any slide change logic here if needed
                }
            }
        });
        
        // Keyboard event handling for space bar (play/pause)
        if (swiperContainer.dataset.swiperKeyboard === 'true') {
            swiperContainer.addEventListener('keydown', function(e) {
                if (e.code === 'Space') {
                    e.preventDefault();
                    if (swiper.autoplay.running) {
                        swiper.autoplay.stop();
                    } else {
                        swiper.autoplay.start();
                    }
                }
            });
        }
    }
});
