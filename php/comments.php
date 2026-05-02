<?php
/**
 * Traitement des commentaires - E-Graphisme
 */
require_once 'config.php';

header('Content-Type: application/json');

// Enable CORS for API calls
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate CSRF token
if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité. Veuillez rafraîchir la page.']);
    exit;
}

// Validate required fields
$required = ['name', 'email', 'comment', 'post_id'];
$errors = [];

foreach ($required as $field) {
    if (empty($input[$field])) {
        $errors[] = "Le champ $field est requis";
    }
}

// Validate email
if (!empty($input['email']) && !validate_email($input['email'])) {
    $errors[] = "Adresse email invalide";
}

// Check for errors
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Erreurs de validation', 'errors' => $errors]);
    exit;
}

// Sanitize data
$data = [
    'post_id' => (int) sanitize($input['post_id']),
    'name' => sanitize($input['name']),
    'email' => sanitize($input['email']),
    'website' => sanitize($input['website'] ?? ''),
    'comment' => sanitize($input['comment']),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'created_at' => date('Y-m-d H:i:s'),
    'status' => 'pending' // pending, approved, spam
];

// Simple CAPTCHA check (time-based)
$comment_time = isset($input['timestamp']) ? (int) $input['timestamp'] : 0;
$current_time = time();
$time_diff = $current_time - $comment_time;

// Block if comment was posted too quickly (likely a bot)
if ($time_diff < 5) {
    echo json_encode(['success' => false, 'message' => 'Veuillez patienter avant de poster un commentaire.']);
    exit;
}

// Save comment to file (for demo - use database in production)
$comments_file = __DIR__ . '/comments.json';
$comments = [];

if (file_exists($comments_file)) {
    $comments = json_decode(file_get_contents($comments_file), true) ?? [];
}

// Add new comment
$comment_id = uniqid('comment_');
$comments[$comment_id] = $data;

// Save comments
if (file_put_contents($comments_file, json_encode($comments, JSON_PRETTY_PRINT))) {
    log_event('COMMENT_CREATED', 'Nouveau commentaire', ['id' => $comment_id, 'post_id' => $data['post_id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Commentaire envoyé avec succès ! Il sera publié après modération.',
        'comment_id' => $comment_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du commentaire.']);
}
