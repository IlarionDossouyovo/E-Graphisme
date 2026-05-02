/**
 * E-Graphisme Supabase Client
 * Database migrations and client for Supabase
 */

// Supabase configuration
const SUPABASE_URL = window.SUPABASE_URL || 'https://your-project.supabase.co';
const SUPABASE_KEY = window.SUPABASE_ANON_KEY || 'your-anon-key';

// Tables to create
const TABLES = {
    // Services table
    services: `
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        category TEXT,
        description TEXT,
        price INTEGER DEFAULT 0,
        currency TEXT DEFAULT 'XOF',
        duration TEXT,
        features JSONB DEFAULT '[]',
        image TEXT,
        popular BOOLEAN DEFAULT false,
        status TEXT DEFAULT 'active',
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    `,
    
    // Products table
    products: `
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        category TEXT,
        description TEXT,
        price INTEGER DEFAULT 0,
        currency TEXT DEFAULT 'XOF',
        images JSONB DEFAULT '[]',
        features JSONB DEFAULT '[]',
        in_stock BOOLEAN DEFAULT true,
        status TEXT DEFAULT 'active',
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    `,
    
    // Contacts/Messages table
    contacts: `
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        company TEXT,
        service TEXT,
        budget TEXT,
        message TEXT,
        status TEXT DEFAULT 'new',
        ip_address TEXT,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    `,
    
    // Newsletter subscribers
    newsletter: `
        id TEXT PRIMARY KEY,
        email TEXT UNIQUE NOT NULL,
        status TEXT DEFAULT 'active',
        created_at TIMESTAMP DEFAULT NOW()
    `,
    
    // Projects (Portfolio)
    projects: `
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        category TEXT,
        client TEXT,
        description TEXT,
        image TEXT,
        images JSONB DEFAULT '[]',
        featured BOOLEAN DEFAULT false,
        status TEXT DEFAULT 'published',
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    `,
    
    // Blog articles
    articles: `
        id TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        category TEXT,
        excerpt TEXT,
        content TEXT,
        image TEXT,
        author TEXT,
        published_at TIMESTAMP DEFAULT NOW(),
        status TEXT DEFAULT 'published',
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    `
};

/**
 * SupabaseClient class
 */
class SupabaseClient {
    constructor(url, key) {
        this.url = url;
        this.key = key;
        this.headers = {
            'apikey': key,
            'Authorization': `Bearer ${key}`,
            'Content-Type': 'application/json',
            'Prefer': 'return=representation'
        };
    }

    /**
     * Make API request
     */
    async request(method, endpoint, body = null) {
        const options = {
            method,
            headers: this.headers
        };
        
        if (body && ['POST', 'PUT', 'PATCH'].includes(method)) {
            options.body = JSON.stringify(body);
        }
        
        const response = await fetch(`${this.url}/rest/v1/${endpoint}`, options);
        
        if (!response.ok) {
            const error = await response.text();
            throw new Error(`Supabase error: ${response.status} - ${error}`);
        }
        
        const text = await response.text();
        return text ? JSON.parse(text) : null;
    }

    // CRUD operations
    async select(table, filters = {}) {
        const query = new URLSearchParams(filters).toString();
        return this.request('GET', `${table}?${query}`);
    }

    async insert(table, data) {
        return this.request('POST', table, data);
    }

    async update(table, id, data) {
        return this.request('PATCH', `${table}?id=eq.${id}`, data);
    }

    async delete(table, id) {
        return this.request('DELETE', `${table}?id=eq.${id}`);
    }

    // Services
    async getServices() {
        return this.select('services', { status: 'eq.active' });
    }

    async getService(id) {
        const results = await this.select('services', { id: `eq.${id}` });
        return results[0] || null;
    }

    async createService(data) {
        return this.insert('services', data);
    }

    // Products
    async getProducts() {
        return this.select('products', { status: 'eq.active' });
    }

    async getProduct(id) {
        const results = await this.select('products', { id: `eq.${id}` });
        return results[0] || null;
    }

    async createProduct(data) {
        return this.insert('products', data);
    }

    // Contacts
    async getContacts(status = null) {
        const filters = status ? { status: `eq.${status}` } : {};
        return this.select('contacts', filters);
    }

    async createContact(data) {
        return this.insert('contacts', {
            ...data,
            id: 'msg_' + Date.now()
        });
    }

    async updateContactStatus(id, status) {
        return this.update('contacts', id, { status, updated_at: new Date().toISOString() });
    }

    // Newsletter
    async getSubscribers() {
        return this.select('newsletter', { status: 'eq.active' });
    }

    async subscribe(email) {
        return this.insert('newsletter', {
            id: 'sub_' + Date.now(),
            email,
            status: 'active'
        });
    }

    // Projects
    async getProjects(category = null) {
        const filters = category ? { category: `eq.${category}` } : {};
        return this.select('projects', { ...filters, status: 'eq.published' });
    }

    // Articles
    async getArticles(category = null) {
        const filters = category ? { category: `eq.${category}` } : {};
        return this.select('articles', { ...filters, status: 'eq.published' });
    }
}

/**
 * Database Manager - handles migration from JSON to Supabase
 */
const DatabaseManager = {
    // Current storage type
    storageType: 'json', // or 'supabase'
    supabase: null,

    /**
     * Initialize database
     */
    async initialize(options = {}) {
        const { useSupabase = false, supabaseUrl, supabaseKey } = options;
        
        if (useSupabase && supabaseUrl && supabaseKey) {
            this.supabase = new SupabaseClient(supabaseUrl, supabaseKey);
            this.storageType = 'supabase';
            
            // Test connection
            try {
                await this.supabase.getServices();
                console.log('✅ Connected to Supabase');
            } catch (e) {
                console.warn('⚠️ Supabase connection failed:', e.message);
                this.storageType = 'json';
            }
        }
        
        return this;
    },

    /**
     * Get services from current storage
     */
    async getServices() {
        if (this.supabase && this.storageType === 'supabase') {
            return this.supabase.getServices();
        }
        // Fallback to JSON
        const response = await fetch('db/services.json');
        return response.json();
    },

    /**
     * Get products from current storage
     */
    async getProducts() {
        if (this.supabase && this.storageType === 'supabase') {
            return this.supabase.getProducts();
        }
        const response = await fetch('db/products.json');
        return response.json();
    },

    /**
     * Get contacts from current storage
     */
    async getContacts() {
        if (this.supabase && this.storageType === 'supabase') {
            return this.supabase.getContacts();
        }
        const response = await fetch('db/contacts.json');
        const data = await response.json();
        return data.contacts || [];
    },

    /**
     * Submit contact form
     */
    async submitContact(data) {
        if (this.supabase && this.storageType === 'supabase') {
            return this.supabase.createContact(data);
        }
        // JSON fallback - just log
        console.log('Contact submitted:', data);
        return { success: true };
    },

    /**
     * Subscribe to newsletter
     */
    async subscribeNewsletter(email) {
        if (this.supabase && this.storageType === 'supabase') {
            return this.supabase.subscribe(email);
        }
        console.log('Newsletter subscription:', email);
        return { success: true };
    },

    /**
     * Get storage status
     */
    getStatus() {
        return {
            storageType: this.storageType,
            connected: this.storageType === 'supabase' && this.supabase !== null
        };
    }
};

/**
 * Initialize with Supabase if configured
 */
(async () => {
    // Check for Supabase config in localStorage or window
    const config = window.EGraphismeConfig || JSON.parse(localStorage.getItem('eg_config') || '{}');
    
    if (config.supabaseUrl && config.supabaseKey) {
        await DatabaseManager.initialize({
            useSupabase: true,
            supabaseUrl: config.supabaseUrl,
            supabaseKey: config.supabaseKey
        });
    }
})();

// Export
window.SupabaseClient = SupabaseClient;
window.DatabaseManager = DatabaseManager;