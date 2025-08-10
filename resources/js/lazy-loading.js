/**
 * Lazy Loading and Image Optimization Utilities
 */

class LazyImageLoader {
    constructor() {
        this.observer = null;
        this.init();
    }

    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                this.handleIntersection.bind(this),
                {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                }
            );

            this.observeImages();
        } else {
            // Fallback for older browsers
            this.loadAllImages();
        }
    }

    observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-srcset]');
        lazyImages.forEach(img => {
            this.observer.observe(img);
        });
    }

    handleIntersection(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }

    loadImage(img) {
        const container = img.closest('.image-container');
        
        // Add loading class
        img.classList.add('loading');
        
        // Create a new image to preload
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            // Set the actual src and srcset
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
            
            // Remove loading state and add loaded state
            img.classList.remove('loading');
            img.classList.add('loaded');
            
            if (container) {
                container.classList.add('loaded');
            }
            
            // Clean up data attributes
            delete img.dataset.src;
            delete img.dataset.srcset;
        };
        
        imageLoader.onerror = () => {
            img.classList.remove('loading');
            img.classList.add('error');
            
            if (container) {
                container.classList.add('loaded');
            }
            
            // Set fallback image if available
            if (img.dataset.fallback) {
                img.src = img.dataset.fallback;
            }
        };
        
        // Start loading
        if (img.dataset.src) {
            imageLoader.src = img.dataset.src;
        }
    }

    loadAllImages() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-srcset]');
        lazyImages.forEach(img => this.loadImage(img));
    }

    // Method to add new images to observation (for dynamic content)
    observeNewImages(container = document) {
        if (this.observer) {
            const newImages = container.querySelectorAll('img[data-src]:not(.observed), img[data-srcset]:not(.observed)');
            newImages.forEach(img => {
                img.classList.add('observed');
                this.observer.observe(img);
            });
        }
    }
}

/**
 * Responsive Image Utilities
 */
class ResponsiveImageHelper {
    static generateSrcSet(basePath, sizes = [768, 1280, 1920]) {
        return sizes.map(size => {
            const path = basePath.replace(/(\.[^.]+)$/, `_${size}w$1`);
            return `${path} ${size}w`;
        }).join(', ');
    }

    static generateSizes(breakpoints = {
        mobile: '(max-width: 767px) 100vw',
        tablet: '(max-width: 1023px) 50vw',
        desktop: '33vw'
    }) {
        return Object.values(breakpoints).join(', ');
    }

    static createOptimizedImage(src, alt, options = {}) {
        const {
            aspectRatio = '16-9',
            sizes = this.generateSizes(),
            className = '',
            loading = 'lazy',
            fallback = null
        } = options;

        const img = document.createElement('img');
        const container = document.createElement('div');
        
        container.className = `image-container aspect-${aspectRatio} ${className}`;
        
        img.className = 'responsive-image lazy-image';
        img.alt = alt;
        img.loading = loading;
        
        if (loading === 'lazy') {
            img.dataset.src = src;
            img.dataset.srcset = this.generateSrcSet(src);
            img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMSIgaGVpZ2h0PSIxIiB2aWV3Qm94PSIwIDAgMSAxIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNGNUY1RjUiLz48L3N2Zz4=';
        } else {
            img.src = src;
            img.srcset = this.generateSrcSet(src);
        }
        
        img.sizes = sizes;
        
        if (fallback) {
            img.dataset.fallback = fallback;
        }
        
        container.appendChild(img);
        return container;
    }
}

/**
 * Performance Monitoring
 */
class PerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.init();
    }

    init() {
        if ('PerformanceObserver' in window) {
            this.observeLCP();
            this.observeCLS();
            this.observeFCP();
        }
    }

    observeLCP() {
        const observer = new PerformanceObserver((list) => {
            const entries = list.getEntries();
            const lastEntry = entries[entries.length - 1];
            this.metrics.lcp = lastEntry.startTime;
            
            // Log warning if LCP is too high
            if (lastEntry.startTime > 2500) {
                console.warn(`LCP is ${lastEntry.startTime}ms, target is <2500ms`);
            }
        });
        
        observer.observe({ entryTypes: ['largest-contentful-paint'] });
    }

    observeCLS() {
        let clsValue = 0;
        
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
            
            this.metrics.cls = clsValue;
            
            // Log warning if CLS is too high
            if (clsValue > 0.1) {
                console.warn(`CLS is ${clsValue}, target is <0.1`);
            }
        });
        
        observer.observe({ entryTypes: ['layout-shift'] });
    }

    observeFCP() {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.name === 'first-contentful-paint') {
                    this.metrics.fcp = entry.startTime;
                }
            }
        });
        
        observer.observe({ entryTypes: ['paint'] });
    }

    getMetrics() {
        return this.metrics;
    }

    logMetrics() {
        console.log('Performance Metrics:', this.metrics);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.lazyImageLoader = new LazyImageLoader();
    window.performanceMonitor = new PerformanceMonitor();
    
    // Log metrics after page load
    window.addEventListener('load', () => {
        setTimeout(() => {
            window.performanceMonitor.logMetrics();
        }, 1000);
    });
});

// Export for module usage
export { LazyImageLoader, ResponsiveImageHelper, PerformanceMonitor };