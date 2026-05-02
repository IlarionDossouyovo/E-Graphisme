/**
 * E-Graphisme Admin API
 * REST API for managing E-Graphisme content
 * Accessible via JavaScript from the frontend
 */

const EGraphismeAPI = {
    baseUrl: '', // Will use relative paths
    version: '1.0.0',

    /**
     * Get all services
     */
    async getServices() {
        try {
            const response = await fetch('db/services.json');
            return await response.json();
        } catch (error) {
            console.error('Error fetching services:', error);
            return [];
        }
    },

    /**
     * Get service by ID
     */
    async getService(id) {
        const services = await this.getServices();
        return services.find(s => s.id === id) || null;
    },

    /**
     * Get all products
     */
    async getProducts() {
        try {
            const response = await fetch('db/products.json');
            return await response.json();
        } catch (error) {
            console.error('Error fetching products:', error);
            return [];
        }
    },

    /**
     * Get product by ID
     */
    async getProduct(id) {
        const products = await this.getProducts();
        return products.find(p => p.id === id) || null;
    },

    /**
     * Get all contacts/messages
     */
    async getContacts() {
        try {
            const response = await fetch('db/contacts.json');
            const data = await response.json();
            return data.contacts || [];
        } catch (error) {
            console.error('Error fetching contacts:', error);
            return [];
        }
    },

    /**
     * Add new contact (simulated - requires backend)
     */
    async addContact(contactData) {
        // In production, this would POST to a PHP endpoint
        console.log('Contact submitted:', contactData);
        return { success: true, message: 'Demande enregistrée ! Nous vous contacterons bientôt.' };
    },

    /**
     * Get newsletter subscribers
     */
    async getSubscribers() {
        try {
            const response = await fetch('db/contacts.json');
            const data = await response.json();
            return data.newsletter || [];
        } catch (error) {
            console.error('Error fetching subscribers:', error);
            return [];
        }
    },

    /**
     * Subscribe to newsletter
     */
    async subscribeNewsletter(email) {
        // In production, this would POST to a PHP endpoint
        console.log('Newsletter subscription:', email);
        return { success: true, message: 'Inscrit ! Merci de votre intérêt.' };
    },

    /**
     * Get site statistics
     */
    async getStats() {
        const [services, products, contacts, subscribers] = await Promise.all([
            this.getServices(),
            this.getProducts(),
            this.getContacts(),
            this.getSubscribers()
        ]);

        return {
            services: services.length,
            products: products.length,
            contacts: contacts.length,
            subscribers: subscribers.length,
            lastUpdate: new Date().toISOString()
        };
    },

    /**
     * Get projects for portfolio
     */
    async getProjects() {
        // Returns portfolio projects (could be from JSON in production)
        return [
            { id: 1, title: 'Logo TechCorp', category: 'branding', client: 'TechCorp SA' },
            { id: 2, title: 'Boutique Mode', category: 'web', client: 'ModePlus' },
            { id: 3, title: 'Campagne Marketing', category: 'print', client: 'Pub Benin' },
            { id: 4, title: 'Café Belle', category: 'branding', client: 'Café Belle' },
            { id: 5, title: 'Restaurant Gourmet', category: 'web', client: 'Gourmet SARL' },
            { id: 6, title: 'Intro Vidéo', category: 'motion', client: 'Electron' },
            { id: 7, title: 'Magazine Lifestyle', category: 'print', client: 'Media Group' },
            { id: 8, title: 'App Mobile', category: 'web', client: 'TechApp' },
            { id: 9, title: 'Event Promo', category: 'motion', client: 'EventCorp' }
        ];
    },

    /**
     * Get blog articles
     */
    async getArticles() {
        return [
            {
                id: 1,
                title: 'Les Tendances du Branding en 2026',
                category: 'Branding',
                date: '15 Avril 2026',
                author: 'Marie Dubois',
                excerpt: 'Découvrez les dernières tendances en matière d\'identité de marque.'
            },
            {
                id: 2,
                title: 'Comment Optimiser son SEO',
                category: 'SEO',
                date: '10 Avril 2026',
                author: 'Jean Kouassi',
                excerpt: 'Guide complet pour améliorer votre référencement naturel.'
            },
            {
                id: 3,
                title: 'L\'IA dans le Design',
                category: 'IA',
                date: '5 Avril 2026',
                author: 'Aimé Sokp',
                excerpt: 'Comment l\'intelligence artificielle transforme le design.'
            },
            {
                id: 4,
                title: 'Initiation au Motion Design',
                category: 'Motion',
                date: '1 Avril 2026',
                author: 'Marie Dubois',
                excerpt: 'Les bases du motion design pour débutants.'
            }
        ];
    },

    /**
     * Export site data
     */
    async exportData() {
        const [services, products, contacts, subscribers, projects, articles] = await Promise.all([
            this.getServices(),
            this.getProducts(),
            this.getContacts(),
            this.getSubscribers(),
            this.getProjects(),
            this.getArticles()
        ]);

        return {
            exportDate: new Date().toISOString(),
            services,
            products,
            contacts,
            subscribers,
            projects,
            articles
        };
    },

    /**
     * Version
     */
    version: '1.0.0'
};

// Utility functions for frontend
const EGraphismeUtils = {
    /**
     * Format price to XOF
     */
    formatPrice(amount) {
        return new Intl.NumberFormat('fr-BJ', {
            style: 'currency',
            currency: 'XOF',
            minimumFractionDigits: 0
        }).format(amount);
    },

    /**
     * Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(date);
    },

    /**
     * Validate email
     */
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Slugify text
     */
    slugify(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    },

    /**
     * Truncate text
     */
    truncate(text, maxLength = 100) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength).trim() + '...';
    },

    /**
     * Debounce function
     */
    debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    /**
     * Scroll to element
     */
    scrollTo(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    },

    /**
     * Copy to clipboard
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch {
            return false;
        }
    },

    /**
     * Get URL parameters
     */
    getUrlParams() {
        const params = new URLSearchParams(window.location.search);
        return Object.fromEntries(params.entries());
    }
};

// Auto-initialize
console.log('🤖 E-Graphisme API loaded');

// Export
window.EGraphismeAPI = EGraphismeAPI;
window.EGraphismeUtils = EGraphismeUtils;