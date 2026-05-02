<?php
/**
 * E-Graphisme API
 * Unified REST API for all services
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Include modules
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/supabase.php';
require_once __DIR__ . '/services.php';
require_once __DIR__ . '/products.php';

// Get endpoint from request
$request = $_SERVER['REQUEST_URI'];
$path = explode('/', trim(explode('?', $request)[0], '/'));

// Remove base path
$endpoint = $path[2] ?? 'home';

// API response
$response = ['success' => false, 'data' => null, 'error' => null];

switch ($endpoint) {
    // Home - API info
    case 'home':
    case '':
        $response = [
            'success' => true,
            'api' => 'E-Graphisme API v1.0',
            'endpoints' => [
                'GET /api/services' => 'List all services',
                'GET /api/services/{slug}' => 'Get service details',
                'GET /api/products' => 'List all products',
                'GET /api/products/{slug}' => 'Get product details',
                'GET /api/categories' => 'List categories',
                'POST /api/contact' => 'Submit contact form',
                'POST /api/quote' => 'Request quote',
                'POST /api/order' => 'Place order'
            ]
        ];
        break;
        
    // Services
    case 'services':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $slug = $path[3] ?? null;
            if ($slug) {
                $service = get_service($slug);
                if ($service) {
                    $response = ['success' => true, 'data' => $service];
                } else {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Service not found'];
                }
            } else {
                $category = $_GET['category'] ?? null;
                if ($category) {
                    $response = ['success' => true, 'data' => get_services_by_category($category)];
                } else {
                    $response = ['success' => true, 'data' => get_services_catalog()];
                }
            }
        }
        break;
        
    // Products
    case 'products':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $slug = $path[3] ?? null;
            if ($slug) {
                $product = get_product_catalog($slug);
                if ($product) {
                    $response = ['success' => true, 'data' => $product];
                } else {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Product not found'];
                }
            } else {
                $category = $_GET['category'] ?? null;
                if ($category) {
                    $response = ['success' => true, 'data' => get_products_by_category($category)];
                } else {
                    $response = ['success' => true, 'data' => get_products_catalog()];
                }
            }
        }
        break;
        
    // Categories
    case 'categories':
        $response = [
            'success' => true,
            'data' => [
                'services' => get_services_categories(),
                'products' => get_products_categories()
            ]
        ];
        break;
        
    // Contact form
    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['name']) && isset($data['email']) && isset($data['message'])) {
                // Save to Supabase if available
                if (function_exists('add_contact')) {
                    $result = add_contact($data['name'], $data['email'], $data['subject'] ?? '', $data['message']);
                    $response = ['success' => true, 'message' => 'Contact submitted successfully'];
                } else {
                    // Save locally
                    $file = __DIR__ . '/../db/contacts.json';
                    $contacts = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
                    $contacts[] = array_merge($data, ['id' => count($contacts) + 1, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')]);
                    file_put_contents($file, json_encode($contacts, JSON_PRETTY_PRINT));
                    $response = ['success' => true, 'message' => 'Contact submitted successfully'];
                }
            } else {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields'];
            }
        }
        break;
        
    // Quote request
    case 'quote':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['service_id']) && isset($data['email'])) {
                $result = request_quote($data['service_id'], $data);
                $response = ['success' => true, 'quote_id' => $result['id'] ?? uniqid('QT-')];
            } else {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields'];
            }
        }
        break;
        
    // Order
    case 'order':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['email'])) {
                $result = place_order($data);
                $response = ['success' => true, 'order_id' => $result['id']];
            } else {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing email'];
            }
        }
        break;
        
    // Default - 404
    default:
        http_response_code(404);
        $response = ['success' => false, 'error' => 'Endpoint not found'];
}

// Send response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);