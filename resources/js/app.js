import './bootstrap';
import './lazy-loading';
import { Swiper } from 'swiper';
import { Navigation, Pagination, Autoplay, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// Configure Swiper with modules first
Swiper.use([Navigation, Pagination, Autoplay, Keyboard]);

// Make Swiper available globally
window.Swiper = Swiper;
console.log('Swiper loaded globally:', typeof window.Swiper !== 'undefined');

// Initialize Swiper when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Starting slider initialization...');
    
    // Initialize all swiper containers
    const swiperContainers = document.querySelectorAll('.swiper-container');
    console.log('Found', swiperContainers.length, 'slider containers');
    
    swiperContainers.forEach(function(swiperContainer, index) {
        if (swiperContainer && !swiperContainer.classList.contains('swiper-initialized')) {
            console.log('Initializing slider:', swiperContainer.id);
            try {
                const slideCount = swiperContainer.querySelectorAll('.swiper-slide').length;
                const swiper = new Swiper(swiperContainer, {
            // Modules are already registered globally
            
            // Slider settings - disable loop for single slides to prevent flickering
            loop: slideCount > 1,
            centeredSlides: true,
            slidesPerView: 1,
            spaceBetween: 0,
            speed: 600,
            effect: 'slide',
            
            // Autoplay configuration
            autoplay: {
                delay: parseInt(swiperContainer.dataset.swiperAutoplay) || 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: swiperContainer.dataset.swiperPauseOnHover === 'true',
            },
            
            // Navigation arrows - target specific to this slider
            navigation: {
                nextEl: swiperContainer.querySelector('.swiper-button-next'),
                prevEl: swiperContainer.querySelector('.swiper-button-prev'),
            },
            
            // Pagination - target specific to this slider
            pagination: {
                el: swiperContainer.querySelector('.swiper-pagination'),
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
                
                console.log('Slider initialized successfully:', swiperContainer.id);
                
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
                
            } catch (error) {
                console.error('Error initializing slider', swiperContainer.id, error);
            }
        }
    });
    
    // Enhanced Form Handling
    initializeFormEnhancements();
});

/**
 * Initialize form enhancements for better UX and accessibility
 */
function initializeFormEnhancements() {
    // Handle all forms
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Skip forms that should handle their own enhancement
        if (form.hasAttribute('data-skip-form-enhancement')) {
            return;
        }
        
        // Skip admin forms by default to avoid CSRF issues
        if (window.location.pathname.startsWith('/admin/')) {
            return;
        }
        
        // Add form submission handling
        form.addEventListener('submit', handleFormSubmission);
        
        // Add real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
            
            // Add accessibility attributes
            enhanceFieldAccessibility(input);
        });
        
        // Add floating label support
        initializeFloatingLabels(form);
    });
    
    // Add global keyboard navigation improvements
    addKeyboardNavigation();
}

/**
 * Handle form submission with loading states
 */
function handleFormSubmission(e) {
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (submitButton) {
        // Add loading state to button
        addLoadingState(submitButton);
        
        // Disable form to prevent double submission
        const formElements = form.querySelectorAll('input, textarea, select, button');
        formElements.forEach(element => element.disabled = true);
        
        // Show loading message
        showFormMessage(form, 'Processing...', 'info');
    }
}

/**
 * Add loading state to buttons
 */
function addLoadingState(button) {
    if (button.classList.contains('btn-loading')) return;
    
    button.classList.add('btn-loading');
    button.setAttribute('disabled', 'true');
    
    const originalText = button.textContent;
    button.dataset.originalText = originalText;
    button.innerHTML = '<span class="opacity-0">' + originalText + '</span>';
}

/**
 * Remove loading state from buttons
 */
function removeLoadingState(button) {
    if (!button.classList.contains('btn-loading')) return;
    
    button.classList.remove('btn-loading');
    button.removeAttribute('disabled');
    
    const originalText = button.dataset.originalText || button.textContent;
    button.innerHTML = originalText;
}

/**
 * Real-time field validation
 */
function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    const fieldContainer = field.closest('.form-group') || field.parentElement;
    
    // Clear previous validation states
    clearFieldValidation(field);
    
    // Check required fields
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation (minimum 8 characters)
    if (field.type === 'password' && value && value.length < 8) {
        showFieldError(field, 'Password must be at least 8 characters long');
        return false;
    }
    
    // If validation passes, show success state
    if (value) {
        showFieldSuccess(field);
    }
    
    return true;
}

/**
 * Clear field error on input
 */
function clearFieldError(e) {
    const field = e.target;
    if (field.classList.contains('error')) {
        clearFieldValidation(field);
    }
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    field.classList.remove('success');
    field.setAttribute('aria-invalid', 'true');
    
    // Remove existing error message
    const existingError = field.parentElement.querySelector('.form-error.dynamic');
    if (existingError) existingError.remove();
    
    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'form-error dynamic';
    errorElement.textContent = message;
    errorElement.setAttribute('role', 'alert');
    
    field.parentElement.appendChild(errorElement);
    
    // Set ARIA describedby
    const errorId = 'error-' + field.name + '-' + Date.now();
    errorElement.id = errorId;
    field.setAttribute('aria-describedby', errorId);
}

/**
 * Show field success
 */
function showFieldSuccess(field) {
    field.classList.add('success');
    field.classList.remove('error');
    field.setAttribute('aria-invalid', 'false');
}

/**
 * Clear field validation
 */
function clearFieldValidation(field) {
    field.classList.remove('error', 'success');
    field.removeAttribute('aria-invalid');
    field.removeAttribute('aria-describedby');
    
    const dynamicError = field.parentElement.querySelector('.form-error.dynamic');
    if (dynamicError) dynamicError.remove();
}

/**
 * Enhance field accessibility
 */
function enhanceFieldAccessibility(field) {
    const label = document.querySelector(`label[for="${field.id}"]`);
    
    if (label && !field.getAttribute('aria-label')) {
        field.setAttribute('aria-label', label.textContent.replace('*', '').trim());
    }
    
    if (field.hasAttribute('required')) {
        field.setAttribute('aria-required', 'true');
    }
}

/**
 * Initialize floating labels
 */
function initializeFloatingLabels(form) {
    const floatingGroups = form.querySelectorAll('.form-floating');
    
    floatingGroups.forEach(group => {
        const input = group.querySelector('.form-input');
        const label = group.querySelector('.form-label');
        
        if (input && label) {
            // Set initial state
            if (input.value) {
                input.classList.add('has-value');
            }
            
            // Handle focus/blur events
            input.addEventListener('focus', () => {
                input.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                input.classList.remove('focused');
                if (input.value) {
                    input.classList.add('has-value');
                } else {
                    input.classList.remove('has-value');
                }
            });
        }
    });
}

/**
 * Show form message
 */
function showFormMessage(form, message, type = 'info') {
    // Remove existing messages
    const existingMessage = form.querySelector('.form-message');
    if (existingMessage) existingMessage.remove();
    
    // Create message element
    const messageElement = document.createElement('div');
    messageElement.className = `form-message alert alert-${type} mb-4`;
    messageElement.textContent = message;
    messageElement.setAttribute('role', 'alert');
    
    // Insert at top of form
    form.insertBefore(messageElement, form.firstChild);
    
    // Auto-remove after delay for info messages
    if (type === 'info') {
        setTimeout(() => {
            if (messageElement.parentElement) {
                messageElement.remove();
            }
        }, 3000);
    }
}

/**
 * Add keyboard navigation improvements
 */
function addKeyboardNavigation() {
    // Handle escape key to clear focus
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.blur) {
                activeElement.blur();
            }
        }
    });
    
    // Handle form navigation with Enter key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.type !== 'textarea') {
            const form = e.target.closest('form');
            if (form) {
                const inputs = Array.from(form.querySelectorAll('input, select, textarea'));
                const currentIndex = inputs.indexOf(e.target);
                const nextInput = inputs[currentIndex + 1];
                
                if (nextInput) {
                    e.preventDefault();
                    nextInput.focus();
                }
            }
        }
    });
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
