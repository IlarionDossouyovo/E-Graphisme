<?php
/**
 * Configuration Admin - E-Graphisme
 * À personnaliser avec vos identifiants
 */

// Configuration de la base de données (si utilisée)
define('DB_HOST', 'localhost');
define('DB_NAME', 'egraphisme');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration admin
define('ADMIN_EMAIL', 'admin@e-graphisme.com');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'egraphisme2026'); // À changer !

// Clé secrète pour les tokens CSRF
define('CSRF_SECRET', 'votre-cle-secrete-a-changer-2026');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Vérifier si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Rediriger si non connecté
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Nettoyer les entrées utilisateur
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Logger les actions admin
 */
function admin_log($action, $details = '') {
    $log_file = __DIR__ . '/logs/admin.log';
    $entry = date('Y-m-d H:i:s') . " | $action | $details | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
    @file_put_contents($log_file, $entry, FILE_APPEND);
}
