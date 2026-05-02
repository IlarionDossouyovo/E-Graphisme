<?php
/**
 * Products Catalog API
 * Manages products for E-Shop
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Define products catalog
function get_products_catalog() {
    return [
        [
            'id' => 'logo-pack',
            'name' => 'Pack Logo Premium',
            'slug' => 'logo-pack',
            'category' => 'branding',
            'description' => 'Pack logo professionnel avec tous formats',
            'price' => 75000,
            'old_price' => 100000,
            'currency' => 'XOF',
            'images' => ['/images/products/logo-pack.svg'],
            'stock' => 100,
            'is_new' => false,
            'is_sale' => true
        ],
        [
            'id' => 'business-card',
            'name' => 'Carte de Visite Premium',
            'slug' => 'business-card',
            'category' => 'branding',
            'description' => 'Cartes de visite刷刷刷刷 haute qualité',
            'price' => 15000,
            'currency' => 'XOF',
            'images' => ['/images/products/business-card.svg'],
            'stock' => 500,
            'is_new' => true
        ],
        [
            'id' => 'website-template',
            'name' => 'Template Site Web',
            'slug' => 'website-template',
            'category' => 'templates',
            'description' => 'Template site web professionnel responsive',
            'price' => 50000,
            'currency' => 'XOF',
            'images' => ['/images/products/website-template.svg'],
            'stock' => 50,
            'is_new' => false
        ],
        [
            'id' => 'social-media-kit',
            'name' => 'Kit Réseaux Sociaux',
            'slug' => 'social-media-kit',
            'category' => 'social',
            'description' => 'Pack complet pour réseaux sociaux',
            'price' => 35000,
            'currency' => 'XOF',
            'images' => ['/images/products/social-kit.svg'],
            'stock' => 200,
            'is_new' => true
        ],
        [
            'id' => 'video-intro',
            'name' => 'Intro Vidéo',
            'slug' => 'video-intro',
            'category' => 'video',
            'description' => 'Intro vidéo professionnelle pour YouTube',
            'price' => 25000,
            'currency' => 'XOF',
            'images' => ['/images/products/video-intro.svg'],
            'stock' => 100,
            'is_new' => false
        ],
        [
            'id' => 'presentation-deck',
            'name' => 'Presentation Deck',
            'slug' => 'presentation-deck',
            'category' => 'presentation',
            'description' => 'Présentation PowerPoint/Keynote professionnelle',
            'price' => 45000,
            'currency' => 'XOF',
            'images' => ['/images/products/presentation.svg'],
            'stock' => 75,
            'is_new' => true
        ],
        [
            'id' => 'brand-guidelines',
            'name' => 'Guide Identité',
            'slug' => 'brand-guidelines',
            'category' => 'branding',
            'description' => 'Guide complet d\'utilisation de la marque',
            'price' => 80000,
            'currency' => 'XOF',
            'images' => ['/images/products/brand-guide.svg'],
            'stock' => 30,
            'is_new' => false
        ],
        [
            'id' => 'ecommerce-starter',
            'name' => 'E-Commerce Starter',
            'slug' => 'ecommerce-starter',
            'category' => 'web',
            'description' => 'Kit démarrage boutique en ligne',
            'price' => 150000,
            'currency' => 'XOF',
            'images' => ['/images/products/ecommerce.svg'],
            'stock' => 20,
            'is_new' => true,
            'is_sale' => true
        ]
    ];
}

// Get product by slug
function get_product_catalog($slug) {
    $products = get_products_catalog();
    foreach ($products as $product) {
        if ($product['slug'] === $slug) {
            return $product;
        }
    }
    return null;
}

// Get products by category
function get_products_by_category($category) {
    $products = get_products_catalog();
    return array_filter($products, function($p) use ($category) {
        return $p['category'] === $category;
    });
}

// Get new products
function get_new_products() {
    $products = get_products_catalog();
    return array_filter($products, function($p) {
        return isset($p['is_new']) && $p['is_new'];
    });
}

// Get sale products
function get_sale_products() {
    $products = get_products_catalog();
    return array_filter($products, function($p) {
        return isset($p['is_sale']) && $p['is_sale'];
    });
}

// Get all categories
function get_products_categories() {
    return ['branding', 'templates', 'social', 'video', 'presentation', 'web'];
}

// Add to cart (session-based)
function add_to_cart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    return $_SESSION['cart'];
}

// Remove from cart
function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
    return $_SESSION['cart'];
}

// Get cart total
function get_cart_total() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    
    $products = get_products_catalog();
    $total = 0;
    
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        foreach ($products as $product) {
            if ($product['id'] === $product_id) {
                $total += $product['price'] * $quantity;
            }
        }
    }
    
    return $total;
}

// Place order
function place_order($customer_data) {
    $order = [
        'id' => 'ORD-' . uniqid(),
        'customer' => $customer_data,
        'items' => $_SESSION['cart'] ?? [],
        'total' => get_cart_total(),
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Save to Supabase if available
    if (function_exists('insert_record')) {
        return insert_record('orders', $order);
    }
    
    return $order;
}