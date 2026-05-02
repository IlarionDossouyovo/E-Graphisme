<?php
/**
 * Configuration PHP - E-Graphisme
 * Fonctions utilitaires et configuration
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données JSON
define('DB_ENABLED', true);
define('DB_TYPE', 'json');
define('DB_PATH', __DIR__ . '/../db/');

// Configuration de la base de données MySQL (optionnel)
define('DB_MYSQL_ENABLED', false);
define('DB_HOST', 'localhost');
define('DB_NAME', 'egraphisme');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuration email
define('EMAIL_FROM', 'noreply@e-graphisme.com');
define('EMAIL_TO', 'contact@e-graphisme.com');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

/**
 * Nettoyer et sécuriser les entrées utilisateur
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Valider une adresse email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Générer un token CSRF
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier le token CSRF
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Logger les événements
 */
function log_event($type, $message, $data = []) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/' . date('Y-m-d') . '.log';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents($log_file, json_encode($entry) . "\n", FILE_APPEND);
}

/**
 * Rediriger vers une URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Afficher un message d'erreur
 */
function show_error($message) {
    return '<div class="alert alert-error">' . $message . '</div>';
}

/**
 * Afficher un message de succès
 */
function show_success($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}
