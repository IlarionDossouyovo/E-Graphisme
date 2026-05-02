<?php
/**
 * Services Catalog API
 * Manages services offered by E-Graphisme
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Define services catalog
function get_services_catalog() {
    return [
        [
            'id' => 'web-design',
            'name' => 'Web Design',
            'slug' => 'web-design',
            'category' => 'design',
            'description' => 'Création de sites web modernes et responsives',
            'price' => 150000,
            'currency' => 'XOF',
            'duration' => '7 jours',
            'features' => [
                'Design personnalisé',
                'Responsive (mobile, tablette, desktop)',
                'SEO optimisé',
                'Formulaire de contact',
                'Intégration réseaux sociaux'
            ],
            'image' => '/images/services/web-design.svg',
            'popular' => true
        ],
        [
            'id' => 'branding',
            'name' => 'Identité Visuelle',
            'slug' => 'branding',
            'category' => 'design',
            'description' => 'Création complète d\'identité de marque',
            'price' => 200000,
            'currency' => 'XOF',
            'duration' => '14 jours',
            'features' => [
                'Logo professionnel',
                'Palette de couleurs',
                'Typographie',
                'Carte de visite',
                'Papeterie'
            ],
            'image' => '/images/services/branding.svg'
        ],
        [
            'id' => 'video-production',
            'name' => 'Production Vidéo IA',
            'slug' => 'video-ia',
            'category' => 'video',
            'description' => 'Création de vidéos propulsées par l\'IA',
            'price' => 250000,
            'currency' => 'XOF',
            'duration' => '5 jours',
            'features' => [
                'Script IA',
                'Voice-over',
                'Sous-titres',
                'Musique royalty-free',
                'Formats multiples'
            ],
            'image' => '/images/services/video.svg',
            'popular' => true
        ],
        [
            'id' => 'seo',
            'name' => 'Référencement SEO',
            'slug' => 'seo',
            'category' => 'marketing',
            'description' => 'Optimisation pour les moteurs de recherche',
            'price' => 100000,
            'currency' => 'XOF',
            'duration' => '10 jours',
            'features' => [
                'Audit SEO',
                'Mots-clés stratégiques',
                'Optimisation technique',
                'Backlinks',
                'Rapport mensuel'
            ],
            'image' => '/images/services/seo.svg'
        ],
        [
            'id' => 'ecommerce',
            'name' => 'E-Commerce',
            'slug' => 'ecommerce',
            'category' => 'web',
            'description' => 'Boutique en ligne complète',
            'price' => 350000,
            'currency' => 'XOF',
            'duration' => '21 jours',
            'features' => [
                'Catalogue produits',
                'Paiement sécurisé',
                'Gestion commandes',
                'Tableau de bord',
                'Support"
            ],
            'image' => '/images/services/ecommerce.svg',
            'popular' => true
        ],
        [
            'id' => 'maintenance',
            'name' => 'Maintenance',
            'slug' => 'maintenance',
            'category' => 'support',
            'description' => 'Maintenance et mise à jour annuelle',
            'price' => 50000,
            'currency' => 'XOF',
            'duration' => '1 an',
            'features' => [
                'Mises à jour',
                'Sécurité',
                'Support 24/7',
                'Sauvegardes',
                'Rapports"
            ],
            'image' => '/images/services/maintenance.svg'
        ]
    ];
}

// Get service by slug
function get_service($slug) {
    $services = get_services_catalog();
    foreach ($services as $service) {
        if ($service['slug'] === $slug) {
            return $service;
        }
    }
    return null;
}

// Get services by category
function get_services_by_category($category) {
    $services = get_services_catalog();
    return array_filter($services, function($s) use ($category) {
        return $s['category'] === $category;
    });
}

// Get popular services
function get_popular_services() {
    $services = get_services_catalog();
    return array_filter($services, function($s) {
        return isset($s['popular']) && $s['popular'];
    });
}

// Get all categories
function get_services_categories() {
    return ['design', 'video', 'marketing', 'web', 'support'];
}

// Format price
function format_price($price, $currency = 'XOF') {
    return number_format($price, 0, ' ', ' ') . ' ' . $currency;
}

// Request service quote
function request_quote($service_id, $client_data) {
    $data = [
        'service_id' => $service_id,
        'client_name' => $client_data['name'],
        'client_email' => $client_data['email'],
        'client_phone' => $client_data['phone'] ?? '',
        'message' => $client_data['message'] ?? '',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Save to quotes table in Supabase if available
    if (function_exists('insert_record')) {
        return insert_record('quotes', $data);
    }
    
    return $data;
}

// API endpoint handler
function handle_services_api($request) {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($request, '/'));
    
    header('Content-Type: application/json');
    
    switch ($method) {
        case 'GET':
            if (isset($path[1])) {
                $service = get_service($path[1]);
                if ($service) {
                    echo json_encode($service);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Service not found']);
                }
            } else {
                echo json_encode(get_services_catalog());
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['service_id']) && isset($data['email'])) {
                $result = request_quote($data['service_id'], $data);
                echo json_encode(['success' => true, 'quote' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}