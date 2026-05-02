<?php
/**
 * API REST - E-Graphisme
 * API pour le blog et les services
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request = $_GET['request'] ?? '';

// Simple router
$routes = [
    'GET articles' => 'api_articles',
    'GET article' => 'api_article',
    'GET categories' => 'api_categories',
    'GET comments' => 'api_comments',
    'POST comment' => 'api_add_comment'
];

// Articles data (simulated - replace with database)
$articles = [
    [
        'id' => 1,
        'title' => 'Les tendances design graphique en 2026',
        'slug' => 'tendances-design-2026',
        'excerpt' => 'Découvrez les principales tendances qui vont marquer le monde du design graphique cette année.',
        'content' => '<p>Le design graphique évolue constamment. En 2026, plusieurs tendances se dégagent...</p>',
        'category' => 'Tendances',
        'date' => '2026-04-15',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/6366f1/ffffff?text=Design+2026',
        'featured' => true
    ],
    [
        'id' => 2,
        'title' => 'Comment créer une identité visuelle forte',
        'slug' => 'creer-identite-visuelle',
        'excerpt' => 'L\'identité visuelle est essentielle pour toute entreprise. Voici nos conseils pour la créer.',
        'content' => '<p>Une identité visuelle forte permet de se différencier de la concurrence...</p>',
        'category' => 'Conseil',
        'date' => '2026-04-10',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/f472b6/ffffff?text=Identité',
        'featured' => false
    ],
    [
        'id' => 3,
        'title' => 'L\'importance du web design responsive',
        'slug' => 'web-design-responsive',
        'excerpt' => 'Avec plus de 60% du trafic web sur mobile, le design responsive est devenu indispensable.',
        'content' => '<p>Le web design responsive permet d\'adapter un site à tous les écrans...</p>',
        'category' => 'Web Design',
        'date' => '2026-04-05',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/22d3ee/ffffff?text=Responsive',
        'featured' => false
    ],
    [
        'id' => 4,
        'title' => 'Les erreurs à éviter en print design',
        'slug' => 'erreurs-print-design',
        'excerpt' => 'Le print design présente des défis uniques. Évitez ces erreurs courantes.',
        'content' => '<p>Le print design nécessite une attention particulière aux détails techniques...</p>',
        'category' => 'Print',
        'date' => '2026-03-28',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/10b981/ffffff?text=Print',
        'featured' => false
    ]
];

// Get all articles
function api_articles() {
    global $articles;
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $category = $_GET['category'] ?? null;
    
    // Filter by category
    $filtered = $articles;
    if ($category) {
        $filtered = array_filter($filtered, fn($a) => $a['category'] === $category);
    }
    
    // Pagination
    $total = count($filtered);
    $offset = ($page - 1) * $limit;
    $paginated = array_slice($filtered, $offset, $limit);
    
    return [
        'success' => true,
        'data' => $paginated,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
}

// Get single article
function api_article() {
    global $articles;
    
    $slug = $_GET['slug'] ?? '';
    
    $article = array_filter($articles, fn($a) => $a['slug'] === $slug);
    
    if (empty($article)) {
        return [
            'success' => false,
            'message' => 'Article non trouvé'
        ];
    }
    
    return [
        'success' => true,
        'data' => reset($article)
    ];
}

// Get categories
function api_categories() {
    global $articles;
    
    $categories = array_unique(array_column($articles, 'category'));
    sort($categories);
    
    return [
        'success' => true,
        'data' => array_map(fn($c) => ['name' => $c, 'slug' => strtolower(str_replace(' ', '-', $c))], $categories)
    ];
}

// Get comments for an article
function api_comments() {
    $post_id = (int)($_GET['post_id'] ?? 0);
    
    if (!$post_id) {
        return ['success' => false, 'message' => 'ID de post requis'];
    }
    
    // Load comments from file
    $comments_file = __DIR__ . '/comments.json';
    $all_comments = [];
    
    if (file_exists($comments_file)) {
        $all_comments = json_decode(file_get_contents($comments_file), true) ?? [];
    }
    
    // Filter by post_id and approved status
    $comments = array_filter($all_comments, fn($c) => $c['post_id'] === $post_id && $c['status'] === 'approved');
    
    // Remove sensitive data
    $comments = array_map(fn($c) => [
        'id' => $c['id'],
        'name' => $c['name'],
        'comment' => $c['comment'],
        'created_at' => $c['created_at']
    ], $comments);
    
    return [
        'success' => true,
        'data' => array_values($comments)
    ];
}

// Add comment
function api_add_comment() {
    // Verify CSRF
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
        return ['success' => false, 'message' => 'Erreur de sécurité'];
    }
    
    // Time-based CAPTCHA
    $comment_time = isset($input['timestamp']) ? (int)$input['timestamp'] : 0;
    if (time() - $comment_time < 3) {
        return ['success' => false, 'message' => 'Veuillez patienter'];
    }
    
    // Validate required fields
    $required = ['name', 'email', 'comment', 'post_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            return ['success' => false, 'message' => "Champ $field requis"];
        }
    }
    
    // Save comment
    $comments_file = __DIR__ . '/comments.json';
    $comments = file_exists($comments_file) ? json_decode(file_get_contents($comments_file), true) ?? [] : [];
    
    $new_comment = [
        'id' => uniqid('comment_'),
        'post_id' => (int)$input['post_id'],
        'name' => sanitize($input['name']),
        'email' => sanitize($input['email']),
        'comment' => sanitize($input['comment']),
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $comments[$new_comment['id']] = $new_comment;
    file_put_contents($comments_file, json_encode($comments, JSON_PRETTY_PRINT));
    
    log_event('COMMENT_CREATED', 'Nouveau commentaire via API', ['post_id' => $input['post_id']]);
    
    return [
        'success' => true,
        'message' => 'Commentaire soumis pour modération'
    ];
}

// Route the request
try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'articles':
            echo json_encode(api_articles());
            break;
            
        case 'article':
            echo json_encode(api_article());
            break;
            
        case 'categories':
            echo json_encode(api_categories());
            break;
            
        case 'comments':
            echo json_encode(api_comments());
            break;
            
        case 'add_comment':
            if ($method === 'POST') {
                echo json_encode(api_add_comment());
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            }
            break;
            
        default:
            // API info
            echo json_encode([
                'success' => true,
                'api' => 'E-Graphisme API v1',
                'endpoints' => [
                    'GET api.php?action=articles' => 'Liste des articles',
                    'GET api.php?action=article&slug=xxx' => 'Article spécifique',
                    'GET api.php?action=categories' => 'Liste des catégories',
                    'GET api.php?action=comments&post_id=xxx' => 'Commentaires d\'un article',
                    'POST api.php?action=add_comment' => 'Ajouter un commentaire'
                ]
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
