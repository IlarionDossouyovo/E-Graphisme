<?php
/**
 * Système d'Upload Sécurisé - E-Graphisme
 * Upload de fichiers avec validation et sécurité
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!verify_csrf($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité']);
    exit;
}

// Rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!check_rate_limit($clientIP, 10, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Trop de requêtes. Veuillez patienter.']);
    exit;
}

// Configuration d'upload
$config = [
    'upload_dir' => __DIR__ . '/../uploads/',
    'max_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/zip'
    ],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'zip']
];

// Créer le répertoire d'upload si pas existant
if (!is_dir($config['upload_dir'])) {
    mkdir($config['upload_dir'], 0755, true);
}

// Fonction pour nettoyer le nom de fichier
function sanitize_filename($filename) {
    // Supprimer les caractères spéciaux
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    // Supprimer les doubles points
    $filename = str_replace('..', '', $filename);
    // Tronquer si trop long
    if (strlen($filename) > 200) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $filename = substr($name, 0, 190) . '.' . $ext;
    }
    return $filename;
}

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier uploadé']);
    exit;
}

$file = $_FILES['file'];
$filename = $file['name'];
$tmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileType = $file['type'];
$fileError = $file['error'];

// Vérifier les erreurs d'upload
if ($fileError !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (limite serveur)',
        UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite formulaire)',
        UPLOAD_ERR_PARTIAL => 'Upload incomplet',
        UPLOAD_ERR_NO_TMP_DIR => 'Erreur serveur',
        UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
        UPLOAD_ERR_EXTENSION => 'Extension non autorisée'
    ];
    echo json_encode(['success' => false, 'message' => $errors[$fileError] ?? 'Erreur d\'upload']);
    exit;
}

// Vérifier la taille
if ($fileSize > $config['max_size']) {
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max: 5MB)']);
    exit;
}

// Vérifier le type MIME
$finfo = new finfo(FILEINFO_MIME_TYPE);
$realMimeType = $finfo->file($tmpName);

if (!in_array($realMimeType, $config['allowed_types'])) {
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']);
    exit;
}

// Vérifier l'extension
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($extension, $config['allowed_extensions'])) {
    echo json_encode(['success' => false, 'message' => 'Extension non autorisée']);
    exit;

// Générer un nom unique
$newFilename = uniqid('upload_', true) . '_' . sanitize_filename($filename);
$destination = $config['upload_dir'] . $newFilename;

// Vérifier que le fichier est bien uploadé
if (!is_uploaded_file($tmpName)) {
    echo json_encode(['success' => false, 'message' => 'Erreur de sécurité']);
    exit;
}

// Déplacer le fichier
if (move_uploaded_file($tmpName, $destination)) {
    // Log l'upload
    log_event('FILE_UPLOADED', 'Fichier uploadé', [
        'filename' => $newFilename,
        'original_name' => $filename,
        'size' => $fileSize,
        'type' => $realMimeType
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Fichier uploadé avec succès',
        'file' => [
            'name' => $newFilename,
            'original_name' => $filename,
            'size' => $fileSize,
            'url' => 'uploads/' . $newFilename
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
}
