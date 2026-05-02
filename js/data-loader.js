// Dynamic Data Loader for E-Graphisme
// Loads services and products from JSON files

async function loadServices() {
    try {
        const response = await fetch('db/services.json');
        const services = await response.json();
        return services;
    } catch (error) {
        console.error('Error loading services:', error);
        return [];
    }
}

async function loadProducts() {
    try {
        const response = await fetch('db/products.json');
        const products = await response.json();
        return products;
    } catch (error) {
        console.error('Error loading products:', error);
        return [];
    }
}

// Render services to container
function renderServices(services, containerId = 'services-container') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = services.map(service => `
        <div class="service-card" data-aos="fade-up">
            <div class="service-icon">
                <i class="${service.icon || 'fas fa-palette'}"></i>
            </div>
            <h3>${service.name}</h3>
            <p>${service.description || ''}</p>
            <span class="service-price">${service.price ? service.price.toLocaleString() + ' XOF' : ''}</span>
            <a href="contact.html" class="btn btn-primary">Commander</a>
        </div>
    `).join('');
}

// Render products to container  
function renderProducts(products, containerId = 'products-container') {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = products.map(product => `
        <div class="product-card">
            <div class="product-image">
                <img src="${product.image || 'images/products/default.jpg'}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p>${product.description || ''}</p>
                <span class="product-price">${product.price ? product.price.toLocaleString() + ' XOF' : ''}</span>
                <a href="contact.html" class="btn btn-secondary">Commander</a>
            </div>
        </div>
    `).join('');
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', async () => {
    // Load services
    if (document.getElementById('services-container')) {
        const services = await loadServices();
        renderServices(services);
    }
    
    // Load products
    if (document.getElementById('products-container')) {
        const products = await loadProducts();
        renderProducts(products);
    }
});

// Export for manual use
window.eGraphismeData = {
    loadServices,
    loadProducts,
    renderServices,
    renderProducts
};