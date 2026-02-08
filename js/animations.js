// Animation helper functions
class Animations {
    // Initialize animations on page load
    static init() {
        this.addScrollAnimations();
        this.addHoverAnimations();
        this.addPageTransition();
    }
    
    // Add scroll animations to elements
    static addScrollAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    
                    // Add specific animation classes based on data attributes
                    const animation = entry.target.dataset.animation;
                    const delay = entry.target.dataset.delay;
                    
                    if (animation) {
                        entry.target.classList.add(animation);
                    }
                    
                    if (delay) {
                        entry.target.style.animationDelay = delay;
                    }
                }
            });
        }, observerOptions);
        
        // Observe elements with animation classes
        document.querySelectorAll('.fade-in-up, .slide-in-left, .slide-in-right').forEach(el => {
            observer.observe(el);
        });
    }
    
    // Add hover animations
    static addHoverAnimations() {
        // Add pulse animation to buttons on hover
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.classList.add('pulse');
            });
            
            button.addEventListener('mouseleave', function() {
                this.classList.remove('pulse');
            });
        });
        
        // Add shake animation to form inputs on error
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('invalid', function() {
                this.classList.add('shake');
                setTimeout(() => {
                    this.classList.remove('shake');
                }, 500);
            });
        });
    }
    
    // Add page transition effect
    static addPageTransition() {
        // Add fade-in class to body on page load
        document.body.classList.add('page-transition');
        
        // Prevent instant navigation
        document.querySelectorAll('a').forEach(link => {
            if (link.href && !link.href.includes('#') && !link.target) {
                link.addEventListener('click', function(e) {
                    // Don't intercept external links or download links
                    if (this.href.includes(window.location.origin) && 
                        !this.download && 
                        !this.href.includes('mailto:') && 
                        !this.href.includes('tel:')) {
                        
                        e.preventDefault();
                        const href = this.href;
                        
                        // Add fade-out animation
                        document.body.style.opacity = '0';
                        document.body.style.transition = 'opacity 0.3s ease';
                        
                        // Navigate after animation
                        setTimeout(() => {
                            window.location.href = href;
                        }, 300);
                    }
                });
            }
        });
    }
    
    // Animate counter
    static animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16); // 60fps
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    // Create loading spinner
    static createSpinner(container) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        container.appendChild(spinner);
        return spinner;
    }
    
    // Remove loading spinner
    static removeSpinner(spinner) {
        if (spinner && spinner.parentElement) {
            spinner.parentElement.removeChild(spinner);
        }
    }
    
    // Show toast notification
    static showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            `;
            document.body.appendChild(container);
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} fade-in-right`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add styles
        toast.style.cssText = `
            background: ${type === 'success' ? '#2ed573' : type === 'error' ? '#ff4757' : '#1e90ff'};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        
        container.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 3000);
    }
    
    // Add parallax effect
    static addParallax() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            
            parallaxElements.forEach(element => {
                const speed = element.dataset.parallaxSpeed || 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
        });
    }
}

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    Animations.init();
    
    // Add parallax to hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.setAttribute('data-parallax', 'true');
        hero.setAttribute('data-parallax-speed', '0.3');
        Animations.addParallax();
    }
    
    // Add loading state to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Reset button after 5 seconds (in case of error)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
});

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Animations;
}