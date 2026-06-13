/**
 * E-Graphisme - Premium Animations
 * GSAP, Particles, Scroll Reveal, Mouse Effects
 */

document.addEventListener('DOMContentLoaded', () => {
    initPremium();
});

function initPremium() {
    initParticles();
    initScrollReveal();
    initNavbar();
    initFilters();
    initHoverEffects();
    initSmoothScroll();
}

// ============================================
// PARTICLES
// ============================================
function initParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    
    const particleCount = 30;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 15 + 's';
        particle.style.animationDuration = (15 + Math.random() * 10) + 's';
        particle.style.width = (2 + Math.random() * 4) + 'px';
        particle.style.height = particle.style.width;
        
        // Random colors from brand palette
        const colors = ['#00D4FF', '#7B2FFF', '#FF006E', '#fff'];
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        container.appendChild(particle);
    }
}

// ============================================
// SCROLL REVEAL (Intersection Observer)
// ============================================
function initScrollReveal() {
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe all animated elements
    document.querySelectorAll('.fade-in, .slide-up, .zoom-in, .stagger').forEach(el => {
        observer.observe(el);
    });
    
    // Auto-add classes to sections
    document.querySelectorAll('section > .container, .service-card, .template-card, .portfolio-item, .testimonial-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
}

// ============================================
// NAVBAR
// ============================================
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            menuToggle.querySelector('i').classList.toggle('fa-bars');
            menuToggle.querySelector('i').classList.toggle('fa-times');
        });
    }
}

// ============================================
// FILTERS (Portfolio, Templates)
// ============================================
function initFilters() {
    document.querySelectorAll('.portfolio-filters, .templates-filters').forEach(filtersContainer => {
        const buttons = filtersContainer.querySelectorAll('.filter-btn');
        const items = filtersContainer.parentElement.querySelectorAll('.portfolio-item, .template-card');
        
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active from all buttons
                buttons.forEach(b => b.classList.remove('active'));
                // Add active to clicked button
                btn.classList.add('active');
                
                const filter = btn.dataset.filter;
                
                items.forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'block';
                        item.classList.add('fade-in');
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
}

// ============================================
// HOVER EFFECTS
// ============================================
function initHoverEffects() {
    // 3D Tilt effect on cards
    document.querySelectorAll('.service-card, .template-card, .portfolio-item').forEach(card => {
        card.addEventListener('mousemove', handleTilt);
        card.addEventListener('mouseleave', resetTilt);
    });
    
    // Button ripple effect
    document.querySelectorAll('.btn-primary, .btn-secondary').forEach(btn => {
        btn.addEventListener('click', createRipple);
    });
}

function handleTilt(e) {
    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const rotateX = (y - centerY) / 10;
    const rotateY = (centerX - x) / 10;
    
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
}

function resetTilt(e) {
    e.currentTarget.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
}

function createRipple(e) {
    const btn = e.currentTarget;
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
    
    btn.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

// ============================================
// SMOOTH SCROLL
// ============================================
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

// ============================================
// GSAP ANIMATIONS (if available)
// ============================================
function initGSAP() {
    if (typeof gsap === 'undefined') return;
    
    gsap.registerPlugin(ScrollTrigger);
    
    // Hero animations
    gsap.from('.hero-logo', { duration: 1, scale: 0, rotation: -180, ease: 'back.out(1.7)' });
    gsap.from('.hero-title', { duration: 1, y: 50, opacity: 0, delay: 0.3 });
    gsap.from('.hero-subtitle', { duration: 1, y: 30, opacity: 0, delay: 0.6 });
    gsap.from('.hero-buttons', { duration: 1, y: 20, opacity: 0, delay: 0.9 });
    
    // Section titles
    gsap.utils.toArray('.section-title').forEach(title => {
        gsap.from(title, {
            scrollTrigger: { trigger: title, start: 'top 80%' },
            duration: 1,
            y: 30,
            opacity: 0
        });
    });
    
    // Service cards stagger
    gsap.from('.service-card', {
        scrollTrigger: { trigger: '.services-grid', start: 'top 80%' },
        duration: 0.8,
        y: 50,
        opacity: 0,
        stagger: 0.1
    });
}

// Initialize GSAP if available
if (typeof gsap !== 'undefined') {
    initGSAP();
}

// Export
window.initPremium = initPremium;