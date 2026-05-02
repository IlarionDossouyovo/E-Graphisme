<?php
// Contact Form Handler - E-Graphisme
// This file processes contact form submissions

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Disable error reporting in production
error_reporting(0);
ini_set('display_errors', 0);

// Start session for CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Simple CAPTCHA check
function verify_captcha($input_time) {
    $current_time = time();
    $time_diff = $current_time - (int)$input_time;
    // Block if form filled too quickly (likely bot)
    return $time_diff >= 3; // Minimum 3 seconds
}

// Rate limiting
function check_rate_limit($ip, $limit = 5, $window = 3600) {
    $rate_file = __DIR__ . '/rate_limit.json';
    $rate_data = [];
    
    if (file_exists($rate_file)) {
        $rate_data = json_decode(file_get_contents($rate_file), true) ?? [];
    }
    
    // Clean old entries
    $now = time();
    foreach ($rate_data as $key => $entry) {
        if ($now - $entry['time'] > $window) {
            unset($rate_data[$key]);
        }
    }
    
    // Check limit
    if (isset($rate_data[$ip]) && $rate_data[$ip]['count'] >= $limit) {
        return false;
    }
    
    // Update rate data
    if (!isset($rate_data[$ip])) {
        $rate_data[$ip] = ['count' => 0, 'time' => $now];
    }
    $rate_data[$ip]['count']++;
    
    file_put_contents($rate_file, json_encode($rate_data));
    return true;
}

// Configuration
$config = [
    'email' => 'contact@e-graphisme.com',
    'subject_prefix' => '[E-Graphisme] ',
    'admin_email' => 'admin@e-graphisme.com',
    'enable_email' => true,
    'log_submissions' => true,
    'log_file' => __DIR__ . '/submissions.log'
];

// Sanitize input function
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Log submission
function logSubmission($data, $ip) {
    global $config;
    if ($config['log_submissions']) {
        $log_entry = date('Y-m-d H:i:s') . " | IP: $ip | Name: {$data['name']} | Email: {$data['email']}\n";
        file_put_contents($config['log_file'], $log_entry, FILE_APPEND);
    }
}

// Send email function
function sendEmail($to, $subject, $message, $headers) {
    $subject = $subject;
    $headers = $headers;
    
    // Try different encoding methods for better compatibility
    if (mail($to, $subject, $message, $headers)) {
        return true;
    }
    
    // Try with UTF-8 encoding
    $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return mail($to, $subject, $message, $headers);
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Required fields
    $required = ['name', 'email', 'subject', 'message'];
    $errors = [];
    
    // Validate required fields
    foreach ($required as $field) {
        if (empty($input[$field])) {
            $errors[] = "Le champ $field est requis";
        }
    }
    
    // Validate email
    if (!empty($input['email']) && !validateEmail($input['email'])) {
        $errors[] = "Adresse email invalide";
    }
    
    // Security: Check CSRF token
    if (!isset($input['csrf_token']) || !verify_csrf($input['csrf_token'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de sécurité. Veuillez rafraîchir la page.'
        ]);
        exit;
    }
    
    // Security: Time-based CAPTCHA (prevent bots)
    if (!isset($input['timestamp']) || !verify_captcha($input['timestamp'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de vérification. Veuillez réessayer.'
        ]);
        exit;
    }
    
    // Security: Rate limiting
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!check_rate_limit($client_ip)) {
        echo json_encode([
            'success' => false,
            'message' => 'Trop de demandes. Veuillez patienter avant de réessayer.'
        ]);
        exit;
    }
    
    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreurs de validation',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Sanitize data
    $data = [
        'name' => sanitize($input['name']),
        'email' => sanitize($input['email']),
        'company' => sanitize($input['company'] ?? ''),
        'phone' => sanitize($input['phone'] ?? ''),
        'service' => sanitize($input['service'] ?? ''),
        'budget' => sanitize($input['budget'] ?? ''),
        'subject' => sanitize($input['subject']),
        'message' => sanitize($input['message']),
        'newsletter' => isset($input['newsletter']) ? 'Oui' : 'Non',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Log submission
    logSubmission($data, $data['ip']);
    
    // Prepare email content
    $email_subject = $config['subject_prefix'] . $data['subject'];
    
    $email_body = "Nouveau message depuis le formulaire de contact E-Graphisme\n";
    $email_body .= "=" . str_repeat("=", 50) . "\n\n";
    $email_body .= "Date: {$data['date']}\n";
    $email_body .= "IP: {$data['ip']}\n\n";
    $email_body .= "INFORMATIONS DU CONTACT\n";
    $email_body .= "-" . str_repeat("-", 30) . "\n";
    $email_body .= "Nom: {$data['name']}\n";
    $email_body .= "Email: {$data['email']}\n";
    
    if (!empty($data['company'])) {
        $email_body .= "Entreprise: {$data['company']}\n";
    }
    if (!empty($data['phone'])) {
        $email_body .= "Téléphone: {$data['phone']}\n";
    }
    if (!empty($data['service'])) {
        $email_body .= "Service interéssé: {$data['service']}\n";
    }
    if (!empty($data['budget'])) {
        $email_body .= "Budget estimé: {$data['budget']}\n";
    }
    
    $email_body .= "\nMESSAGE\n";
    $email_body .= "-" . str_repeat("-", 30) . "\n";
    $email_body .= $data['message'] . "\n\n";
    $email_body .= "Newsletter: {$data['newsletter']}\n";
    $email_body .= "=" . str_repeat("=", 50) . "\n";
    $email_body .= "Envoyé depuis le formulaire de contact E-Graphisme\n";
    
    // Email headers
    $headers = "From: {$data['name']} <noreply@e-graphisme.com>\r\n";
    $headers .= "Reply-To: {$data['email']}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    
    // Send email
    if ($config['enable_email']) {
        $email_sent = sendEmail($config['email'], $email_subject, $email_body, $headers);
        
        // Send confirmation email to user
        $confirm_subject = "Confirmation de votre message - E-Graphisme";
        $confirm_body = "Bonjour {$data['name']},\n\n";
        $confirm_body .= "Nous avons bien reçu votre message et nous vous remercions de l'intérêt que vous portez à nos services.\n\n";
        $confirm_body .= "Voici un résumé de votre demande:\n";
        $confirm_body .= "-" . str_repeat("-", 30) . "\n";
        $confirm_body .= "Sujet: {$data['subject']}\n";
        $confirm_body .= "Message: {$data['message']}\n\n";
        $confirm_body .= "Notre équipe vous répondra dans les plus brefs délais, généralement sous 24-48 heures.\n\n";
        $confirm_body .= "Cordialement,\n";
        $confirm_body .= "L'équipe E-Graphisme\n\n";
        $confirm_body .= "---\n";
        $confirm_body .= "E-Graphisme - Design Graphique & Web\n";
        $confirm_body .= "www.e-graphisme.com";
        
        $confirm_headers = "From: E-Graphisme <noreply@e-graphisme.com>\r\n";
        $confirm_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        sendEmail($data['email'], $confirm_subject, $confirm_body, $confirm_headers);
    } else {
        $email_sent = true; // Consider success if email disabled
    }
    
    // Return success response
    if ($email_sent) {
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès !',
            'data' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi du message. Veuillez réessayer.'
        ]);
    }
    
} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>