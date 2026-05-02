/**
 * E-Graphisme Notification System
 * Toast notifications, alerts, and feedback
 */

const Notifications = {
    // Default configuration
    config: {
        duration: 4000,
        position: 'top-right',
        maxVisible: 5,
        animation: 'slide'
    },

    // Active notifications
    notifications: [],

    /**
     * Show a notification
     */
    show(options = {}) {
        const {
            type = 'info', // success, error, warning, info
            title = '',
            message = '',
            duration = this.config.duration,
            dismissible = true,
            actions = []
        } = options;

        // Create notification element
        const id = 'notif-' + Date.now();
        const notification = {
            id,
            type,
            title,
            message,
            createdAt: Date.now()
        };

        // Add to array
        this.notifications.push(notification);

        // Limit visible notifications
        if (this.notifications.length > this.config.maxVisible) {
            const oldest = this.notifications.shift();
            this.remove(oldest.id);
        }

        // Create DOM element
        const element = this.createElement(notification);
        document.body.appendChild(element);

        // Show animation
        requestAnimationFrame(() => {
            element.classList.add('show');
        });

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }

        return id;
    },

    /**
     * Create notification DOM element
     */
    createElement(notification) {
        const el = document.createElement('div');
        el.id = notification.id;
        el.className = `eg-notification eg-notification-${notification.type}`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        el.innerHTML = `
            <div class="eg-notification-icon">
                <i class="fas ${icons[notification.type]}"></i>
            </div>
            <div class="eg-notification-content">
                ${notification.title ? `<div class="eg-notification-title">${notification.title}</div>` : ''}
                ${notification.message ? `<div class="eg-notification-message">${notification.message}</div>` : ''}
            </div>
            <button class="eg-notification-close" onclick="Notifications.dismiss('${notification.id}')">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add styles
        if (!document.getElementById('eg-notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'eg-notification-styles';
            styles.textContent = `
                .eg-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    max-width: 380px;
                    padding: 16px 20px;
                    background: #1a1a2e;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    z-index: 11000;
                    transform: translateX(120%);
                    transition: transform 0.3s ease;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                .eg-notification.show {
                    transform: translateX(0);
                }
                .eg-notification-success {
                    border-left: 4px solid #00c853;
                }
                .eg-notification-success .eg-notification-icon {
                    color: #00c853;
                }
                .eg-notification-error {
                    border-left: 4px solid #ff1744;
                }
                .eg-notification-error .eg-notification-icon {
                    color: #ff1744;
                }
                .eg-notification-warning {
                    border-left: 4px solid #ffab00;
                }
                .eg-notification-warning .eg-notification-icon {
                    color: #ffab00;
                }
                .eg-notification-info {
                    border-left: 4px solid #00d4ff;
                }
                .eg-notification-info .eg-notification-icon {
                    color: #00d4ff;
                }
                .eg-notification-icon {
                    font-size: 20px;
                    margin-top: 2px;
                }
                .eg-notification-content {
                    flex: 1;
                }
                .eg-notification-title {
                    font-weight: 600;
                    color: white;
                    font-size: 14px;
                    margin-bottom: 4px;
                }
                .eg-notification-message {
                    font-size: 13px;
                    color: rgba(255, 255, 255, 0.7);
                    line-height: 1.4;
                }
                .eg-notification-close {
                    background: none;
                    border: none;
                    color: rgba(255, 255, 255, 0.5);
                    cursor: pointer;
                    padding: 4px;
                    font-size: 14px;
                }
                .eg-notification-close:hover {
                    color: white;
                }
                @media (max-width: 480px) {
                    .eg-notification {
                        left: 10px;
                        right: 10px;
                        max-width: none;
                    }
                }
            `;
            document.head.appendChild(styles);
        }

        return el;
    },

    /**
     * Dismiss notification
     */
    dismiss(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('show');
            setTimeout(() => this.remove(id), 300);
        }
    },

    /**
     * Remove notification
     */
    remove(id) {
        const el = document.getElementById(id);
        if (el) {
            el.remove();
        }
        this.notifications = this.notifications.filter(n => n.id !== id);
    },

    /**
     * Clear all notifications
     */
    clear() {
        this.notifications.forEach(n => this.dismiss(n.id));
    },

    // Convenience methods
    success(title, message) {
        return this.show({ type: 'success', title, message });
    },

    error(title, message) {
        return this.show({ type: 'error', title, message, duration: 6000 });
    },

    warning(title, message) {
        return this.show({ type: 'warning', title, message, duration: 5000 });
    },

    info(title, message) {
        return this.show({ type: 'info', title, message });
    }
};

// Contact form handler
const ContactForm = {
    /**
     * Initialize contact form
     */
    initialize() {
        const form = document.getElementById('contact-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.submit(form);
        });
    },

    /**
     * Submit contact form
     */
    async submit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        // Show loading
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        }

        // Collect form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            // Use API
            if (window.EGraphismeAPI) {
                const result = await EGraphismeAPI.addContact(data);
                
                if (result.success) {
                    Notifications.success('Message envoyé !', result.message);
                    form.reset();
                } else {
                    Notifications.error('Erreur', result.message || 'Une erreur est survenue');
                }
            } else {
                // Simulate success
                await new Promise(r => setTimeout(r, 1000));
                Notifications.success('Message envoyé !', 'Nous vous contacterons sous 24h. Merci !');
                form.reset();
            }
        } catch (error) {
            Notifications.error('Erreur', 'Une erreur est survenue. Veuillez réessayer.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }
};

// Newsletter form handler
const NewsletterForm = {
    initialize() {
        const forms = document.querySelectorAll('.newsletter-form, .newsletter-form-inline');
        
        forms.forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = form.querySelector('input[type="email"]')?.value;
                
                if (!email || !EGraphismeUtils?.isValidEmail(email)) {
                    Notifications.warning('Email invalide', 'Veuillez entrer une adresse email valide');
                    return;
                }

                const btn = form.querySelector('button');
                const originalText = btn?.innerHTML;
                
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }

                try {
                    if (window.EGraphismeAPI) {
                        await EGraphismeAPI.subscribeNewsletter(email);
                    }
                    Notifications.success('Abonné !', 'Merci de votre inscription à la newsletter.');
                    form.reset();
                } catch (error) {
                    Notifications.error('Erreur', 'Impossible de s\'abonner');
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                }
            });
        });
    }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    ContactForm.initialize();
    NewsletterForm.initialize();
});

// Export
window.Notifications = Notifications;
window.ContactForm = ContactForm;
window.NewsletterForm = NewsletterForm;