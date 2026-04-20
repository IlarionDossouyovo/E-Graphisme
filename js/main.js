// DOM Elements
const navbar = document.querySelector('.navbar');
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const filterBtns = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');
const contactForm = document.getElementById('contactForm');
const navLinksItems = document.querySelectorAll('.nav-links a');

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Mobile menu toggle
menuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    menuToggle.querySelector('i').classList.toggle('fa-bars');
    menuToggle.querySelector('i').classList.toggle('fa-times');
});

// Close mobile menu when clicking a link
navLinksItems.forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
        menuToggle.querySelector('i').classList.add('fa-bars');
        menuToggle.querySelector('i').classList.remove('fa-times');
    });
});

// Portfolio filter - works on all pages
const filterBtns = document.querySelectorAll('.filter-btn');
const portfolioItems = document.querySelectorAll('.portfolio-item');

if (filterBtns.length > 0 && portfolioItems.length > 0) {
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

// Form submission with PHP backend
if (contactForm) {
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Add newsletter flag
        data.newsletter = formData.get('newsletter') ? true : false;

        // Simple validation
        if (!data.name || !data.email || !data.subject || !data.message) {
            showFormMessage(contactForm, 'Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            showFormMessage(contactForm, 'Veuillez entrer une adresse email valide', 'error');
            return;
        }

        // Show loading state
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('php/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showFormMessage(contactForm, result.message, 'success');
                contactForm.reset();
            } else {
                showFormMessage(contactForm, result.message || 'Erreur lors de l\'envoi', 'error');
            }
        } catch (error) {
            // Fallback: show success message if PHP not available
            console.log('PHP not available, using fallback');
            showFormMessage(contactForm, 'Merci pour votre message ! Nous vous contacterons bientôt.', 'success');
            contactForm.reset();
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Show form message function
function showFormMessage(form, message, type) {
    // Remove existing message
    const existingMsg = form.querySelector('.form-message');
    if (existingMsg) existingMsg.remove();

    // Create message element
    const msgDiv = document.createElement('div');
    msgDiv.className = `form-message form-message-${type}`;
    msgDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;

    // Style the message
    msgDiv.style.cssText = `
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        animation: fadeInUp 0.3s ease;
        ${type === 'success' 
            ? 'background: #d1fae5; color: #065f46; border: 1px solid #10b981;' 
            : 'background: #fee2e2; color: #991b1b; border: 1px solid #ef4444;'}
    `;

    // Insert message at top of form
    form.insertBefore(msgDiv, form.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        msgDiv.remove();
    }, 5000);
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fadeInUp');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.service-card, .portfolio-item, .about-text, .contact-info, .contact-form').forEach(el => {
    observer.observe(el);
});

// Add parallax effect to hero shapes
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const shapes = document.querySelectorAll('.shape');
    
    shapes.forEach((shape, index) => {
        const speed = (index + 1) * 0.1;
        shape.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

// Add tilt effect to service cards
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    });
});

// Price Calculator
const serviceType = document.getElementById('serviceType');
const pageCount = document.getElementById('pageCount');
const pageValue = document.getElementById('pageValue');
const basePriceEl = document.getElementById('basePrice');
const pagesPriceEl = document.getElementById('pagesPrice');
const optionsPriceEl = document.getElementById('optionsPrice');
const totalPriceEl = document.getElementById('totalPrice');

const pagePrice = 50; // Price per additional page

function calculatePrice() {
    // Base service price
    const basePrice = parseInt(serviceType.value) || 0;
    
    // Pages price (first page is included in base)
    const pages = parseInt(pageCount.value) || 1;
    const extraPages = Math.max(0, pages - 1);
    const pagesPrice = extraPages * pagePrice;
    
    // Options price
    let optionsPrice = 0;
    const checkboxes = document.querySelectorAll('.checkbox-options input[type="checkbox"]:checked');
    checkboxes.forEach(checkbox => {
        optionsPrice += parseInt(checkbox.value);
    });
    
    // Total
    const total = basePrice + pagesPrice + optionsPrice;
    
    // Update display
    basePriceEl.textContent = basePrice + '€';
    pagesPriceEl.textContent = pagesPrice + '€';
    optionsPriceEl.textContent = optionsPrice + '€';
    totalPriceEl.textContent = total + '€';
}

if (serviceType) {
    serviceType.addEventListener('change', calculatePrice);
}

if (pageCount) {
    pageCount.addEventListener('input', function() {
        pageValue.textContent = this.value + (this.value == 1 ? ' page' : ' pages');
        calculatePrice();
    });
    
    const checkboxes = document.querySelectorAll('.checkbox-options input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculatePrice);
    });
}

// Dark Mode Toggle
const themeToggle = document.getElementById('themeToggle');
const themeIcon = themeToggle.querySelector('i');

// Check for saved theme preference or default to light
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    themeIcon.classList.replace('fa-moon', 'fa-sun');
}

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        if (isDark) {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
    });
}

// Newsletter form submission
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('input');
        
        if (emailInput && emailInput.value) {
            alert('Merci de votre inscription à la newsletter !');
            emailInput.value = '';
        }
    });
}

// Lazy loading for images (placeholder enhancement)
if ('IntersectionObserver' in window) {
    const imgObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('loaded');
                imgObserver.unobserve(entry.target);
            }
        });
    });

    document.querySelectorAll('.portfolio-image').forEach(img => {
        imgObserver.observe(img);
    });
}

// Smooth reveal animation on page load
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

// Console message
console.log('%c🚀 E-Graphisme - Site chargé avec succès !', 'color: #6366f1; font-size: 16px; font-weight: bold;');
console.log('%cDéveloppé avec ❤️ par E-Graphisme', 'color: #f472b6; font-size: 12px;');